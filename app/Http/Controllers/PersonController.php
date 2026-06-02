<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePersonRequest;
use App\Http\Requests\UpdatePersonRequest;
use App\Models\Activity;
use App\Models\CalendarEvent;
use App\Models\Deal;
use App\Models\Entity;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class PersonController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        $people = Person::query()
            ->with('entity:id,name')
            ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
            ->when($request->string('q')->toString(), fn ($q, $term) =>
                $q->where(fn ($w) => $w->where('name', 'like', "%$term%")
                    ->orWhere('email', 'like', "%$term%")))
            ->when($request->integer('entity_id'), fn ($q, $id) => $q->where('entity_id', $id))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('people/Index', [
            'people'  => $people,
            'filters' => $request->only(['q', 'entity_id']),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Person::class);
        $user = request()->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        return Inertia::render('people/Create', [
            'entities' => Entity::query()
                ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
                ->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(StorePersonRequest $request)
    {
        $person = Person::create([
            ...$request->validated(),
            'owner_id' => $request->user()->id,
        ]);
        return redirect()->route('people.show', $person)->with('success', 'Pessoa criada.');
    }

    public function show(Person $person)
    {
        Gate::authorize('view', $person);
        $person->load(['entity', 'deals.stage', 'events']);
        return Inertia::render('people/Show', ['person' => $person]);
    }

    public function edit(Person $person)
    {
        Gate::authorize('update', $person);
        $user = request()->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        return Inertia::render('people/Edit', [
            'person'   => $person,
            'entities' => Entity::query()
                ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
                ->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(UpdatePersonRequest $request, Person $person)
    {
        $person->update($request->validated());
        return redirect()->route('people.show', $person)->with('success', 'Pessoa atualizada.');
    }

    public function destroy(Person $person)
    {
        Gate::authorize('delete', $person);
        $person->delete();
        return redirect()->route('people.index')->with('success', 'Pessoa removida.');
    }

    /**
     * Merge a duplicate person into this one. The "loser" person has its
     * Deals, CalendarEvents and Activities re-pointed to the "winner" and
     * is then soft-deleted. Missing fields on the winner inherit from loser.
     */
    public function merge(Request $request, Person $person)
    {
        Gate::authorize('update', $person);

        $data = $request->validate([
            'duplicate_id' => ['required', 'integer', 'different:'.$person->id, 'exists:people,id'],
        ]);

        $duplicate = Person::findOrFail($data['duplicate_id']);
        Gate::authorize('update', $duplicate);

        DB::transaction(function () use ($person, $duplicate) {
            // Inherit non-empty fields when the winner has them blank.
            $merged = [];
            foreach (['email', 'phone', 'position', 'entity_id'] as $f) {
                if (blank($person->{$f}) && filled($duplicate->{$f})) {
                    $merged[$f] = $duplicate->{$f};
                }
            }
            $merged['notes'] = trim(($person->notes ?? '')."\n--- Merge de #{$duplicate->id} ({$duplicate->name}) ---\n".($duplicate->notes ?? ''));
            $person->update($merged);

            Deal::where('person_id', $duplicate->id)->update(['person_id' => $person->id]);
            CalendarEvent::where('eventable_type', Person::class)
                ->where('eventable_id', $duplicate->id)
                ->update(['eventable_id' => $person->id]);
            Activity::where('subject_type', Person::class)
                ->where('subject_id', $duplicate->id)
                ->update(['subject_id' => $person->id]);

            $duplicate->delete();
        });

        return redirect()->route('people.show', $person)->with('success', 'Pessoas combinadas.');
    }
}
