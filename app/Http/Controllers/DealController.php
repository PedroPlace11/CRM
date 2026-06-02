<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDealRequest;
use App\Http\Requests\UpdateDealRequest;
use App\Jobs\SendFollowUpEmail;
use App\Models\Activity;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Entity;
use App\Models\FollowUpSequence;
use App\Models\Person;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class DealController extends Controller
{
    /**
     * Kanban board view: deals grouped by stage.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        $stages = DealStage::orderBy('position')->get();

        $deals = Deal::query()
            ->with(['entity:id,name', 'person:id,name', 'owner:id,name'])
            ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
            ->when($isAdmin && $request->integer('owner_id'), fn ($q, $id) => $q->where('owner_id', $id))
            ->when($request->string('q')->toString(), fn ($q, $term) => $q->where('title', 'like', "%$term%"))
            ->when($request->integer('stage_id'), fn ($q, $id) => $q->where('stage_id', $id))
            ->when($request->date('expected_from'), fn ($q, $d) => $q->whereDate('expected_close_date', '>=', $d))
            ->when($request->date('expected_to'), fn ($q, $d) => $q->whereDate('expected_close_date', '<=', $d))
            ->when($request->filled('min_value'), fn ($q) => $q->where('value', '>=', (float) $request->input('min_value')))
            ->when($request->filled('max_value'), fn ($q) => $q->where('value', '<=', (float) $request->input('max_value')))
            ->orderBy('updated_at', 'desc')
            ->get();

        $byStage = $stages->mapWithKeys(fn ($s) => [
            $s->id => [
                'stage'      => $s,
                'deals'      => $deals->where('stage_id', $s->id)->values(),
                'total_value'=> (float) $deals->where('stage_id', $s->id)->sum('value'),
            ],
        ]);

        return Inertia::render('deals/Board', [
            'stages'  => $stages,
            'columns' => $byStage,
            'filters' => $request->only(['q', 'owner_id', 'stage_id', 'expected_from', 'expected_to', 'min_value', 'max_value']),
            'owners'  => $isAdmin
                ? User::orderBy('name')->get(['id', 'name'])
                : User::where('id', $user->id)->get(['id', 'name']),
        ]);
    }

    public function create()
    {
        Gate::authorize('create', Deal::class);
        $user = request()->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        return Inertia::render('deals/Create', [
            'stages'   => DealStage::orderBy('position')->get(['id', 'name']),
            'entities' => Entity::query()
                ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
                ->orderBy('name')->get(['id', 'name']),
            'people'   => Person::query()
                ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
                ->orderBy('name')->get(['id', 'name', 'entity_id']),
        ]);
    }

    public function store(StoreDealRequest $request)
    {
        $deal = Deal::create([
            ...$request->validated(),
            'owner_id'        => $request->user()->id,
            'last_activity_at'=> now(),
        ]);
        return redirect()->route('deals.show', $deal)->with('success', 'Negócio criado.');
    }

    public function show(Deal $deal)
    {
        Gate::authorize('view', $deal);
        $deal->load([
            'entity', 'person', 'owner', 'stage',
            'products', 'proposals', 'emails.sender', 'followUpSequence',
            'events', 'activities.user',
        ]);

        return Inertia::render('deals/Show', [
            'deal'   => $deal,
            'stages' => DealStage::orderBy('position')->get(['id', 'name', 'is_won', 'is_lost', 'is_follow_up']),
        ]);
    }

    public function edit(Deal $deal)
    {
        Gate::authorize('update', $deal);
        $user = request()->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        return Inertia::render('deals/Edit', [
            'deal'     => $deal,
            'stages'   => DealStage::orderBy('position')->get(['id', 'name']),
            'entities' => Entity::query()
                ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
                ->orderBy('name')->get(['id', 'name']),
            'people'   => Person::query()
                ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
                ->orderBy('name')->get(['id', 'name', 'entity_id']),
        ]);
    }

    public function update(UpdateDealRequest $request, Deal $deal)
    {
        $previousStage = $deal->stage_id;
        $deal->update([...$request->validated(), 'last_activity_at' => now()]);

        if ($previousStage !== $deal->stage_id) {
            Activity::create([
                'subject_type' => Deal::class,
                'subject_id'   => $deal->id,
                'user_id'      => $request->user()->id,
                'type'         => 'stage_change',
                'title'        => "Estágio alterado para {$deal->stage->name}",
                'happened_at'  => now(),
                'meta'         => ['from' => $previousStage, 'to' => $deal->stage_id],
            ]);
            $this->syncFollowUpSequence($deal);
        }

        return redirect()->route('deals.show', $deal)->with('success', 'Negócio atualizado.');
    }

    /**
     * Move deal to another stage (Kanban drag-and-drop).
     */
    public function move(Request $request, Deal $deal)
    {
        Gate::authorize('update', $deal);
        $request->validate(['stage_id' => ['required', 'exists:deal_stages,id']]);

        $previous = $deal->stage_id;
        $deal->update(['stage_id' => $request->integer('stage_id'), 'last_activity_at' => now()]);

        Activity::create([
            'subject_type' => Deal::class,
            'subject_id'   => $deal->id,
            'user_id'      => $request->user()->id,
            'type'         => 'stage_change',
            'title'        => 'Estágio alterado',
            'happened_at'  => now(),
            'meta'         => ['from' => $previous, 'to' => $deal->stage_id],
        ]);

        $this->syncFollowUpSequence($deal);

        return back();
    }

    /**
     * Start/stop FollowUpSequence based on the current deal stage.
     * Triggered both by move() and update() when stage_id changes.
     */
    private function syncFollowUpSequence(Deal $deal): void
    {
        $deal->refresh()->load('stage');
        $isFollowUp = (bool) $deal->stage?->is_follow_up;

        $active = FollowUpSequence::where('deal_id', $deal->id)->where('status', 'active')->first();

        if ($isFollowUp && ! $active) {
            $sequence = FollowUpSequence::create([
                'deal_id'           => $deal->id,
                'status'            => 'active',
                'started_at'        => now(),
                'next_send_at'      => $this->firstBusinessSendAt(),
                'sent_count'        => 0,
                'used_template_ids' => [],
            ]);
            SendFollowUpEmail::dispatch($sequence->id)->delay($sequence->next_send_at);
        } elseif (! $isFollowUp && $active) {
            $active->update([
                'status'      => 'stopped',
                'stopped_at'  => now(),
                'stop_reason' => 'stage_changed',
            ]);
        }
    }

    private function firstBusinessSendAt(): \Carbon\Carbon
    {
        $next = now()->addMinutes(30);
        if ($next->hour < 9) $next->setTime(10, 0);
        if ($next->hour >= 18) $next->addDay()->setTime(10, 0);
        while ($next->isWeekend()) $next->addDay();
        return $next;
    }

    public function destroy(Deal $deal)
    {
        Gate::authorize('delete', $deal);
        $deal->delete();
        return redirect()->route('deals.index')->with('success', 'Negócio removido.');
    }

    /**
     * Manually stop the active follow-up sequence for a deal.
     * Used both for explicit "cancel" and for "client replied" signals.
     */
    public function cancelFollowUp(Request $request, Deal $deal)
    {
        Gate::authorize('update', $deal);
        $reason = $request->input('reason') === 'client_replied' ? 'client_replied' : 'cancelled_by_user';

        $sequence = FollowUpSequence::where('deal_id', $deal->id)->where('status', 'active')->first();
        if ($sequence) {
            $sequence->update([
                'status'      => 'stopped',
                'stopped_at'  => now(),
                'stop_reason' => $reason,
            ]);
        }

        Activity::create([
            'subject_type' => Deal::class,
            'subject_id'   => $deal->id,
            'user_id'      => $request->user()->id,
            'type'         => 'note',
            'title'        => $reason === 'client_replied'
                ? 'Cliente respondeu — sequência de follow-up parada.'
                : 'Sequência de follow-up cancelada.',
            'happened_at'  => now(),
        ]);

        return back()->with('success', 'Sequência de follow-up parada.');
    }
}
