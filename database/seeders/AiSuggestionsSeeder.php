<?php

namespace Database\Seeders;

use App\Models\AiSuggestion;
use App\Models\Deal;
use App\Models\Entity;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;

class AiSuggestionsSeeder extends Seeder
{
    public function run(): void
    {
        $userId = User::where('email', 'admin@crm.test')->value('id');

        if (! $userId) {
            return;
        }

        $dealAurora = Deal::where('title', 'Licencas CRM Aurora')->first();
        $dealBrisa = Deal::where('title', 'Consultoria Comercial Brisa')->first();
        $personInes = Person::where('email', 'ines.almeida@brisaconsultoria.pt')->first();
        $entityDelta = Entity::where('name', 'Delta Obras')->first();

        $suggestions = [
            [
                'title' => 'Agendar chamada de follow-up com Aurora',
                'reason' => 'Sem atividade registrada ha 4 dias em um negocio com valor elevado.',
                'action_type' => 'call',
                'priority' => 'high',
                'suggested_date' => now()->addDay()->toDateString(),
                'subject_type' => $dealAurora?->getMorphClass() ?? Deal::class,
                'subject_id' => $dealAurora?->id,
            ],
            [
                'title' => 'Enviar email de reforco para proposta Brisa',
                'reason' => 'A proposta foi enviada e ainda nao houve resposta do decisor principal.',
                'action_type' => 'email',
                'priority' => 'high',
                'suggested_date' => now()->addDays(2)->toDateString(),
                'subject_type' => $dealBrisa?->getMorphClass() ?? Deal::class,
                'subject_id' => $dealBrisa?->id,
            ],
            [
                'title' => 'Marcar reuniao executiva com Ines Almeida',
                'reason' => 'Lead com alto potencial e historico de abertura para reunioes estrategicas.',
                'action_type' => 'meeting',
                'priority' => 'normal',
                'suggested_date' => now()->addDays(3)->toDateString(),
                'subject_type' => $personInes?->getMorphClass() ?? Person::class,
                'subject_id' => $personInes?->id,
            ],
            [
                'title' => 'Criar tarefa de qualificacao para Delta Obras',
                'reason' => 'Conta ativa com interesse em integracao ERP e sem proxima acao definida.',
                'action_type' => 'follow_up',
                'priority' => 'normal',
                'suggested_date' => now()->addDays(1)->toDateString(),
                'subject_type' => $entityDelta?->getMorphClass() ?? Entity::class,
                'subject_id' => $entityDelta?->id,
            ],
            [
                'title' => 'Preparar proposta comercial personalizada',
                'reason' => 'Sinais de compra aumentaram apos interacoes recentes no funil.',
                'action_type' => 'proposal',
                'priority' => 'high',
                'suggested_date' => now()->addDays(4)->toDateString(),
                'subject_type' => $dealAurora?->getMorphClass() ?? Deal::class,
                'subject_id' => $dealAurora?->id,
            ],
        ];

        foreach ($suggestions as $suggestion) {
            AiSuggestion::updateOrCreate(
                [
                    'user_id' => $userId,
                    'title' => $suggestion['title'],
                ],
                [
                    'subject_type' => $suggestion['subject_type'],
                    'subject_id' => $suggestion['subject_id'],
                    'action_type' => $suggestion['action_type'],
                    'reason' => $suggestion['reason'],
                    'suggested_date' => $suggestion['suggested_date'],
                    'priority' => $suggestion['priority'],
                    'status' => 'pending',
                    'decided_at' => null,
                ]
            );
        }
    }
}
