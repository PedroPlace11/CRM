<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCalendarEventRequest;
use App\Http\Requests\UpdateCalendarEventRequest;
use App\Models\CalendarEvent;
use App\Models\Deal;
use App\Models\Entity;
use App\Models\Person;
use App\Services\Integrations\GoogleServiceAccountCalendarClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CalendarEventController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        // Returns events for FullCalendar in a given range.
        $start = $request->date('start') ?? now()->startOfMonth();
        $end   = $request->date('end')   ?? now()->endOfMonth();

        $events = CalendarEvent::query()
            ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
            ->where('start_at', '>=', $start)
            ->where('start_at', '<=', $end)
            ->with('eventable')
            ->get();

        if ($request->wantsJson()) {
            return response()->json($events);
        }

        return Inertia::render('calendar/Index', ['events' => $events]);
    }

    public function create()
    {
        Gate::authorize('create', CalendarEvent::class);
        $user = request()->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        return Inertia::render('calendar/Create', [
            'entities' => Entity::query()
                ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
                ->orderBy('name')->get(['id', 'name']),
            'people'   => Person::query()
                ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
                ->orderBy('name')->get(['id', 'name']),
            'deals'    => Deal::query()
                ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
                ->orderBy('title')->get(['id', 'title']),
        ]);
    }

    public function store(StoreCalendarEventRequest $request, GoogleServiceAccountCalendarClient $googleClient)
    {
        $data = $request->validated();
        $eventableMap = [
            'entity' => Entity::class,
            'person' => Person::class,
            'deal'   => Deal::class,
        ];

        if (! empty($data['eventable_type']) && ! empty($data['eventable_id'])) {
            $data['eventable_type'] = $eventableMap[$data['eventable_type']] ?? null;
        } else {
            $data['eventable_type'] = null;
            $data['eventable_id']   = null;
        }

        return DB::transaction(function () use ($data, $request, $googleClient) {
            $event = CalendarEvent::create([
                ...$data,
                'owner_id' => $request->user()->id,
            ]);

            if ($googleClient->isConfigured()) {
                $googleClient->upsertEvent($event);
            }

            return $request->wantsJson()
                ? response()->json($event, 201)
                : redirect()->route('calendar.show', $event);
        });
    }

    public function show(CalendarEvent $event)
    {
        Gate::authorize('view', $event);
        $event->load(['eventable', 'attendees.attendee', 'owner']);
        return Inertia::render('calendar/Show', ['event' => $event]);
    }

    public function update(UpdateCalendarEventRequest $request, CalendarEvent $event, GoogleServiceAccountCalendarClient $googleClient)
    {
        return DB::transaction(function () use ($request, $event, $googleClient) {
            $event->update($request->validated());

            if ($googleClient->isConfigured()) {
                $googleClient->upsertEvent($event);
            }

            return back()->with('success', 'Evento atualizado.');
        });
    }

    public function destroy(CalendarEvent $event, GoogleServiceAccountCalendarClient $googleClient)
    {
        Gate::authorize('delete', $event);

        return DB::transaction(function () use ($event, $googleClient) {
            if ($googleClient->isConfigured()) {
                $googleClient->deleteEvent($event);
            }

            $event->delete();

            return redirect()->route('calendar.index')->with('success', 'Evento removido.');
        });
    }
}
