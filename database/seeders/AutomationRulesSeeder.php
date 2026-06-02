<?php

namespace Database\Seeders;

use App\Models\AutomationRule;
use App\Models\DealStage;
use App\Models\User;
use Illuminate\Database\Seeder;

class AutomationRulesSeeder extends Seeder
{
    public function run(): void
    {
        $creatorId = User::where('email', 'admin@crm.test')->value('id');

        $stages = DealStage::query()
            ->get(['id', 'slug'])
            ->keyBy('slug');

        $rules = [
            [
                'name' => 'Follow-up em leads parados 5 dias',
                'active' => true,
                'trigger' => [
                    'type' => 'deal_inactive',
                    'days' => 5,
                    'stage_id' => $stages['lead']->id ?? null,
                ],
                'action' => [
                    'type' => 'create_activity',
                    'activity_type' => 'call',
                    'priority' => 'high',
                ],
                'last_run_at' => now()->subDays(1),
            ],
            [
                'name' => 'Lembrete de proposta sem retorno',
                'active' => true,
                'trigger' => [
                    'type' => 'deal_inactive',
                    'days' => 4,
                    'stage_id' => $stages['proposta']->id ?? null,
                ],
                'action' => [
                    'type' => 'create_activity',
                    'activity_type' => 'task',
                    'priority' => 'high',
                ],
                'last_run_at' => now()->subHours(8),
            ],
            [
                'name' => 'Check-in semanal em negociacao',
                'active' => true,
                'trigger' => [
                    'type' => 'deal_inactive',
                    'days' => 7,
                    'stage_id' => $stages['negociacao']->id ?? null,
                ],
                'action' => [
                    'type' => 'create_activity',
                    'activity_type' => 'meeting',
                    'priority' => 'normal',
                ],
                'last_run_at' => now()->subDays(2),
            ],
            [
                'name' => 'Reativar oportunidade em follow-up',
                'active' => false,
                'trigger' => [
                    'type' => 'deal_inactive',
                    'days' => 10,
                    'stage_id' => $stages['follow-up']->id ?? null,
                ],
                'action' => [
                    'type' => 'create_activity',
                    'activity_type' => 'call',
                    'priority' => 'normal',
                ],
                'last_run_at' => now()->subDays(6),
            ],
            [
                'name' => 'Tarefa de revisao para negocios ganhos',
                'active' => true,
                'trigger' => [
                    'type' => 'deal_inactive',
                    'days' => 3,
                    'stage_id' => $stages['ganho']->id ?? null,
                ],
                'action' => [
                    'type' => 'create_activity',
                    'activity_type' => 'task',
                    'priority' => 'normal',
                ],
                'last_run_at' => now()->subHours(20),
            ],
        ];

        foreach ($rules as $rule) {
            AutomationRule::updateOrCreate(
                ['name' => $rule['name']],
                [
                    'active' => $rule['active'],
                    'trigger' => $rule['trigger'],
                    'action' => $rule['action'],
                    'created_by' => $creatorId,
                    'last_run_at' => $rule['last_run_at'],
                ]
            );
        }
    }
}
