<?php

namespace App\Jobs;

use App\Models\AutomationRule;
use App\Models\AutomationRun;
use App\Models\CalendarEvent;
use App\Models\Deal;
use App\Notifications\AutomationActivityCreated;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Evaluates automation rules and triggers actions for deals matching conditions.
 * Today only supports trigger.type = 'deal_inactive' with `days` and optional `stage_id`,
 * and action.type = 'create_activity' with `activity_type` and `priority`.
 */
class EvaluateAutomationRules implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        foreach (AutomationRule::where('active', true)->get() as $rule) {
            $this->run($rule);
            $rule->update(['last_run_at' => now()]);
        }
    }

    private function run(AutomationRule $rule): void
    {
        $trigger = $rule->trigger;
        $action  = $rule->action;

        if (($trigger['type'] ?? null) !== 'deal_inactive') return;

        $threshold = now()->subDays((int) ($trigger['days'] ?? 7));

        $query = Deal::query()->where(function ($q) use ($threshold) {
            $q->whereNull('last_activity_at')->orWhere('last_activity_at', '<=', $threshold);
        });
        if (! empty($trigger['stage_id'])) $query->where('stage_id', $trigger['stage_id']);

        foreach ($query->get() as $deal) {
            if (($action['type'] ?? null) !== 'create_activity') continue;
            if (! $deal->owner_id) continue;

            $event = CalendarEvent::create([
                'eventable_type' => Deal::class,
                'eventable_id'   => $deal->id,
                'owner_id'       => $deal->owner_id,
                'title'          => "Retomar contacto: {$deal->title}",
                'description'    => "Negócio sem atividade há {$trigger['days']} dias (regra: {$rule->name}).",
                'type'           => $action['activity_type'] ?? 'task',
                'priority'       => $action['priority'] ?? 'high',
                'start_at'       => now()->addDay()->setTime(10, 0),
            ]);

            // Internal notification to the deal owner (mail + database).
            if ($deal->owner) {
                $deal->owner->notify(new AutomationActivityCreated($rule, $event));
            }

            AutomationRun::create([
                'automation_rule_id' => $rule->id,
                'deal_id'            => $deal->id,
                'status'             => 'success',
                'message'            => 'Activity created.',
                'ran_at'             => now(),
            ]);
        }
    }
}
