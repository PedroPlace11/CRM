<?php

namespace App\Http\Controllers;

use App\Models\AiSuggestion;
use App\Models\CalendarEvent;
use App\Models\Deal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AiSuggestionController extends Controller
{
    public function index(Request $request)
    {
        $suggestions = AiSuggestion::where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->with('subject')
            ->latest()
            ->get();

        return Inertia::render('ai/Suggestions', [
            'suggestions' => $suggestions,
        ]);
    }

    public function decide(Request $request, AiSuggestion $suggestion)
    {
        abort_unless($suggestion->user_id === $request->user()->id, 403);
        $request->validate(['decision' => ['required', 'in:accepted,dismissed,snoozed']]);

        $decision = $request->input('decision');

        $suggestion->update([
            'status'     => $decision,
            'decided_at' => now(),
        ]);

        if ($decision === 'accepted') {
            $this->materialiseSuggestion($suggestion);
        }

        return back()->with('success', 'Sugestão registada.');
    }

    /**
     * Convert an accepted suggestion into a real CalendarEvent on the user's calendar.
     */
    private function materialiseSuggestion(AiSuggestion $suggestion): void
    {
        $start = ($suggestion->suggested_date ?: now()->addDay())
            ->copy()->setTime(10, 0);

        $deal = $suggestion->subject_type === Deal::class
            ? Deal::find($suggestion->subject_id)
            : null;

        CalendarEvent::create([
            'eventable_type' => $suggestion->subject_type,
            'eventable_id'   => $suggestion->subject_id,
            'owner_id'       => $suggestion->user_id,
            'title'          => $suggestion->title,
            'description'    => $suggestion->reason
                . ($deal ? "\n\nNegócio: {$deal->title}" : ''),
            'type'           => $suggestion->action_type ?: 'task',
            'priority'       => $suggestion->priority ?: 'normal',
            'start_at'       => $start,
            'end_at'         => $start->copy()->addMinutes(30),
        ]);
    }
}
