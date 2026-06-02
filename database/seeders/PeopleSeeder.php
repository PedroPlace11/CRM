<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;

class PeopleSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::where('email', 'admin@crm.test')->value('id');

        $entities = Entity::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->keyBy('name');

        $people = [
            [
                'entity' => 'Aurora Tech Lda',
                'name' => 'Mariana Costa',
                'email' => 'mariana.costa@auroratech.pt',
                'phone' => '+351 910 000 101',
                'position' => 'Diretora Comercial',
                'notes' => 'Prefere contacto por email.',
            ],
            [
                'entity' => 'Aurora Tech Lda',
                'name' => 'Rui Ferreira',
                'email' => 'rui.ferreira@auroratech.pt',
                'phone' => '+351 910 000 102',
                'position' => 'Gestor de Compras',
                'notes' => 'Precisa de proposta trimestral.',
            ],
            [
                'entity' => 'Brisa Consultoria',
                'name' => 'Ines Almeida',
                'email' => 'ines.almeida@brisaconsultoria.pt',
                'phone' => '+351 910 000 201',
                'position' => 'CEO',
                'notes' => 'Reuniao marcada para quinta-feira.',
            ],
            [
                'entity' => 'Cobalto Retail',
                'name' => 'Tiago Sousa',
                'email' => 'tiago.sousa@cobaltoretail.pt',
                'phone' => '+351 910 000 301',
                'position' => 'Diretor de Operacoes',
                'notes' => 'Conta suspensa, aguarda reativacao.',
            ],
            [
                'entity' => 'Delta Obras',
                'name' => 'Sara Carvalho',
                'email' => 'sara.carvalho@delta.pt',
                'phone' => '+351 910 000 401',
                'position' => 'Responsavel Tecnica',
                'notes' => 'Interessada em integracao ERP.',
            ],
            [
                'entity' => 'Estrela Media',
                'name' => 'Pedro Lopes',
                'email' => 'pedro.lopes@estrelamedia.pt',
                'phone' => '+351 910 000 501',
                'position' => 'Marketing Manager',
                'notes' => 'Enviar case study.',
            ],
        ];

        foreach ($people as $person) {
            $entityId = $entities[$person['entity']]->id ?? null;

            Person::updateOrCreate(
                ['email' => $person['email']],
                [
                    'entity_id' => $entityId,
                    'owner_id' => $ownerId,
                    'name' => $person['name'],
                    'phone' => $person['phone'],
                    'position' => $person['position'],
                    'notes' => $person['notes'],
                ]
            );
        }
    }
}
