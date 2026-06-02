<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEntityRequest;
use App\Http\Requests\UpdateEntityRequest;
use App\Models\Entity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class EntityController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        $entities = Entity::query()
            ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
            ->when($request->string('q')->toString(), fn ($q, $term) =>
                $q->where(fn ($w) => $w->where('name', 'like', "%$term%")
                    ->orWhere('vat', 'like', "%$term%")
                    ->orWhere('email', 'like', "%$term%")))
            ->when($request->string('status')->toString(), fn ($q, $s) => $q->where('status', $s))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('entities/Index', [
            'entities' => $entities,
            'filters'  => $request->only(['q', 'status']),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Entity::class);
        return Inertia::render('entities/Create');
    }

    public function store(StoreEntityRequest $request)
    {
        $entity = Entity::create([
            ...$request->validated(),
            'owner_id' => $request->user()->id,
        ]);

        return redirect()->route('entities.show', $entity)->with('success', 'Entidade criada.');
    }

    public function show(Entity $entity)
    {
        Gate::authorize('view', $entity);
        $entity->load(['owner', 'people', 'deals.stage']);

        return Inertia::render('entities/Show', [
            'entity' => $entity,
        ]);
    }

    public function edit(Entity $entity)
    {
        Gate::authorize('update', $entity);
        return Inertia::render('entities/Edit', ['entity' => $entity]);
    }

    public function update(UpdateEntityRequest $request, Entity $entity)
    {
        $entity->update($request->validated());
        return redirect()->route('entities.show', $entity)->with('success', 'Entidade atualizada.');
    }

    public function destroy(Entity $entity)
    {
        Gate::authorize('delete', $entity);
        $entity->delete();
        return redirect()->route('entities.index')->with('success', 'Entidade removida.');
    }
}
