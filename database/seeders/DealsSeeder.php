<?php

namespace Database\Seeders;

use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Entity;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;

class DealsSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::where('email', 'admin@crm.test')->value('id');

        $stages = DealStage::query()
            ->get(['id', 'slug'])
            ->keyBy('slug');

        $entities = Entity::query()
            ->get(['id', 'name'])
            ->keyBy('name');

        $people = Person::query()
            ->get(['id', 'email'])
            ->keyBy('email');

        $deals = [
            [
                'title' => 'Licencas CRM Aurora',
                'entity' => 'Aurora Tech Lda',
                'person' => 'mariana.costa@auroratech.pt',
                'stage' => 'lead',
                'value' => 18500,
                'probability' => 20,
                'expected_close_date' => now()->addDays(20),
                'source' => 'Inbound',
                'notes' => 'Primeiro contacto por formulario do site.',
            ],
            [
                'title' => 'Consultoria Comercial Brisa',
                'entity' => 'Brisa Consultoria',
                'person' => 'ines.almeida@brisaconsultoria.pt',
                'stage' => 'proposta',
                'value' => 9200,
                'probability' => 50,
                'expected_close_date' => now()->addDays(35),
                'source' => 'Referral',
                'notes' => 'Proposta enviada e aguardando feedback.',
            ],
            [
                'title' => 'Renovacao Retail Cobalto',
                'entity' => 'Cobalto Retail',
                'person' => 'tiago.sousa@cobaltoretail.pt',
                'stage' => 'negociacao',
                'value' => 15400,
                'probability' => 65,
                'expected_close_date' => now()->addDays(15),
                'source' => 'Upsell',
                'notes' => 'Negociar desconto para renovacao anual.',
            ],
            [
                'title' => 'Follow up Delta Obras',
                'entity' => 'Delta Obras',
                'person' => 'sara.carvalho@delta.pt',
                'stage' => 'follow-up',
                'value' => 11000,
                'probability' => 40,
                'expected_close_date' => now()->addDays(10),
                'source' => 'Outbound',
                'notes' => 'Precisa de alinhamento tecnico.',
            ],
            [
                'title' => 'Pacote Marketing Estrela',
                'entity' => 'Estrela Media',
                'person' => 'pedro.lopes@estrelamedia.pt',
                'stage' => 'ganho',
                'value' => 7600,
                'probability' => 100,
                'expected_close_date' => now()->subDays(5),
                'source' => 'Partner',
                'notes' => 'Contrato assinado.',
            ],
            [
                'title' => 'Projeto Integracao Cobalto',
                'entity' => 'Cobalto Retail',
                'person' => 'tiago.sousa@cobaltoretail.pt',
                'stage' => 'perdido',
                'value' => 19800,
                'probability' => 0,
                'expected_close_date' => now()->subDays(12),
                'source' => 'RFP',
                'notes' => 'Perdido para concorrente.',
            ],
        ];

        foreach ($deals as $deal) {
            $entityId = $entities[$deal['entity']]->id ?? null;
            $personId = $people[$deal['person']]->id ?? null;
            $stageId = $stages[$deal['stage']]->id ?? null;

            if (!$stageId) {
                continue;
            }

            Deal::updateOrCreate(
                ['title' => $deal['title']],
                [
                    'entity_id' => $entityId,
                    'person_id' => $personId,
                    'owner_id' => $ownerId,
                    'stage_id' => $stageId,
                    'value' => $deal['value'],
                    'probability' => $deal['probability'],
                    'expected_close_date' => $deal['expected_close_date'],
                    'source' => $deal['source'],
                    'notes' => $deal['notes'],
                    'last_activity_at' => now(),
                ]
            );
        }
    }
}
