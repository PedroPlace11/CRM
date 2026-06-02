<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Deal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ActivityController extends Controller
{
    /**
     * Quick-create activity from the deal detail screen.
     */
    public function storeForDeal(Request $request, Deal $deal)
    {
        Gate::authorize('view', $deal);

        $data = $request->validate([
            'type'        => ['required', 'in:call,task,meeting,note,email'],
            'title'       => ['required', 'string', 'max:191'],
            'body'        => ['nullable', 'string'],
            'happened_at' => ['nullable', 'date'],
        ]);

        $activity = Activity::create([
            'subject_type' => Deal::class,
            'subject_id'   => $deal->id,
            'user_id'      => $request->user()->id,
            'type'         => $data['type'],
            'title'        => $data['title'],
            'body'         => $data['body'] ?? null,
            'happened_at'  => $data['happened_at'] ?? now(),
        ]);

        $deal->update(['last_activity_at' => $activity->happened_at]);

        return back()->with('success', 'Atividade registada.');
    }

    /**
     * Timeline for deal: returns merged feed of activities + emails + events ordered desc.
     * Optional query params:
     *   - kinds[]      : subset of ['activity','email','event'] (default: all)
     *   - types[]      : activity types to include (call,task,meeting,note,email,stage_change)
     *   - from / to    : ISO date filters on the entry timestamp
     */
    public function timelineForDeal(Request $request, Deal $deal)
    {
        Gate::authorize('view', $deal);

        $kinds = (array) $request->input('kinds', ['activity', 'email', 'event']);
        $types = (array) $request->input('types', []);
        $from  = $request->date('from');
        $to    = $request->date('to');

        $entries = collect();

        if (in_array('activity', $kinds, true)) {
            $entries = $entries->merge(
                $deal->activities()
                    ->with('user:id,name')
                    ->when($types, fn ($q) => $q->whereIn('type', $types))
                    ->when($from, fn ($q) => $q->where('happened_at', '>=', $from))
                    ->when($to,   fn ($q) => $q->where('happened_at', '<=', $to))
                    ->latest('happened_at')->get()
                    ->map(fn ($a) => ['kind' => 'activity', 'at' => $a->happened_at, 'data' => $a])
            );
        }
        if (in_array('email', $kinds, true)) {
            $entries = $entries->merge(
                $deal->emails()
                    ->with('sender:id,name')
                    ->when($from, fn ($q) => $q->where('sent_at', '>=', $from))
                    ->when($to,   fn ($q) => $q->where('sent_at', '<=', $to))
                    ->latest('sent_at')->get()
                    ->map(fn ($e) => ['kind' => 'email', 'at' => $e->sent_at, 'data' => $e])
            );
        }
        if (in_array('event', $kinds, true)) {
            $entries = $entries->merge(
                $deal->events()
                    ->with('owner:id,name')
                    ->when($from, fn ($q) => $q->where('start_at', '>=', $from))
                    ->when($to,   fn ($q) => $q->where('start_at', '<=', $to))
                    ->latest('start_at')->get()
                    ->map(fn ($ev) => ['kind' => 'event', 'at' => $ev->start_at, 'data' => $ev])
            );
        }

        return response()->json($entries->sortByDesc('at')->values());
    }
}
