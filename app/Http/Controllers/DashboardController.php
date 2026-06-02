<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\AiSuggestion;
use App\Models\AutomationRule;
use App\Models\CalendarEvent;
use App\Models\Deal;
use App\Models\DealStage;
use App\Models\LeadSubmission;
use App\Models\Product;
use App\Models\PublicForm;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);

        $closedStageIds = DealStage::query()
            ->where('is_won', true)
            ->orWhere('is_lost', true)
            ->pluck('id');

        $openDealsBase = Deal::query()
            ->whereNotIn('stage_id', $closedStageIds, 'and')
            ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id));

        $activeDeals = (clone $openDealsBase)->count('*');
        $activeDealsThisWeek = (clone $openDealsBase)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()], 'and')
            ->count('*');
        $openPipelineValue = (float) ((clone $openDealsBase)->sum('value') ?: 0);

        $upcomingEvents = CalendarEvent::query()
            ->whereBetween('start_at', [now(), now()->copy()->addHours(48)], 'and')
            ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
            ->orderBy('start_at', 'asc')
            ->limit(4)
            ->get(['id', 'title', 'start_at']);

        $activitiesToday = Activity::query()
            ->whereDate('happened_at', '=', today(), 'and')
            ->when(! $isAdmin, fn ($q) => $q->where('user_id', $user->id))
            ->count('*');

        $leadScope = LeadSubmission::query()
            ->when(! $isAdmin, fn ($q) => $q->whereHas('form', fn ($fq) => $fq->where('owner_id', $user->id)));

        $leadsLast7Days = (clone $leadScope)
            ->where('submitted_at', '>=', now()->copy()->subDays(7))
            ->count('*');

        $leadsPrevious7Days = (clone $leadScope)
            ->whereBetween('submitted_at', [now()->copy()->subDays(14), now()->copy()->subDays(7)], 'and')
            ->count('*');

        $leadsDelta = $leadsLast7Days - $leadsPrevious7Days;

        $advanceScope = Deal::query()
            ->where('created_at', '>=', now()->copy()->subDays(30))
            ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id));

        $wonIn30Days = (clone $advanceScope)
            ->whereHas('stage', fn ($q) => $q->where('is_won', true))
            ->count('*');

        $closedIn30Days = (clone $advanceScope)
            ->whereHas('stage', fn ($q) => $q->where('is_won', true)->orWhere('is_lost', true))
            ->count('*');

        $advanceRate = $closedIn30Days > 0
            ? (int) round(($wonIn30Days / $closedIn30Days) * 100)
            : 0;

        $stages = DealStage::query()
            ->orderBy('position', 'asc')
            ->get(['id', 'name']);

        $dealCountsByStage = Deal::query()
            ->selectRaw('stage_id, COUNT(*) as aggregate')
            ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
            ->groupBy('stage_id')
            ->pluck('aggregate', 'stage_id');

        $totalDealsInDistribution = (int) $dealCountsByStage->sum();

        $stageDistribution = $stages->map(function ($stage) use ($dealCountsByStage, $totalDealsInDistribution) {
            $count = (int) ($dealCountsByStage[$stage->id] ?? 0);
            $percentage = $totalDealsInDistribution > 0
                ? max(8, (int) round(($count / $totalDealsInDistribution) * 100))
                : 8;

            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'count' => $count,
                'percentage' => $percentage,
            ];
        })->all();

        $activeAutomationRules = AutomationRule::query()
            ->where('active', true)
            ->when(! $isAdmin, fn ($q) => $q->where('created_by', $user->id))
            ->count('*');

        $automationRulesTotal = AutomationRule::query()
            ->when(! $isAdmin, fn ($q) => $q->where('created_by', $user->id))
            ->count('*');

        $activePublicForms = PublicForm::query()
            ->where('active', true)
            ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
            ->count('*');

        $publicFormsTotal = PublicForm::query()
            ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
            ->count('*');

        $pendingSuggestions = AiSuggestion::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->count('*');

        $activeProducts = Product::query()
            ->where('active', true)
            ->count('*');

        return Inertia::render('Dashboard', [
            'hero' => [
                'activeDeals' => $activeDeals,
                'activeDealsThisWeek' => $activeDealsThisWeek,
                'openPipelineValue' => $openPipelineValue,
                'upcomingEvents' => $upcomingEvents,
            ],
            'summary' => [
                'activitiesToday' => $activitiesToday,
                'leadsLast7Days' => $leadsLast7Days,
                'leadsDelta' => $leadsDelta,
                'advanceRate' => $advanceRate,
                'pendingSuggestions' => $pendingSuggestions,
                'activeAutomationRules' => $activeAutomationRules,
                'automationRulesTotal' => $automationRulesTotal,
                'activePublicForms' => $activePublicForms,
                'publicFormsTotal' => $publicFormsTotal,
                'activeProducts' => $activeProducts,
            ],
            'stageDistribution' => $stageDistribution,
        ]);
    }
}
