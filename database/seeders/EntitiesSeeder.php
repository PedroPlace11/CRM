<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\User;
use Illuminate\Database\Seeder;

class EntitiesSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::where('email', 'admin@crm.test')->value('id');

        $entities = [
            [
                'name' => 'Aurora Tech Lda',
                'vat' => 'PT509000111',
                'email' => 'contacto@auroratech.pt',
                'phone' => '+351 210 000 111',
                'address' => 'Rua do Sol 10, Lisboa',
                'status' => 'active',
                'notes' => 'Cliente SaaS com foco em automacao comercial.',
            ],
            [
                'name' => 'Brisa Consultoria',
                'vat' => 'PT509000222',
                'email' => 'hello@brisaconsultoria.pt',
                'phone' => '+351 220 000 222',
                'address' => 'Av. Atlantica 55, Porto',
                'status' => 'active',
                'notes' => 'Conta com equipa de 12 vendedores.',
            ],
            [
                'name' => 'Cobalto Retail',
                'vat' => 'PT509000333',
                'email' => 'suporte@cobaltoretail.pt',
                'phone' => '+351 230 000 333',
                'address' => 'Rua da Estacao 4, Aveiro',
                'status' => 'inactive',
                'notes' => 'Em pausa, renovar contacto no proximo trimestre.',
            ],
            [
                'name' => 'Delta Obras',
                'vat' => 'PT509000444',
                'email' => 'obras@delta.pt',
                'phone' => '+351 240 000 444',
                'address' => 'Estrada Nacional 2, Coimbra',
                'status' => 'active',
                'notes' => 'Precisa de integracao com ERP.',
            ],
            [
                'name' => 'Estrela Media',
                'vat' => 'PT509000555',
                'email' => 'contato@estrelamedia.pt',
                'phone' => '+351 250 000 555',
                'address' => 'Rua da Fonte 77, Braga',
                'status' => 'active',
                'notes' => 'Campanha piloto para 3 meses.',
            ],
        ];

        foreach ($entities as $entity) {
            Entity::updateOrCreate(
                ['name' => $entity['name']],
                array_merge($entity, ['owner_id' => $ownerId])
            );
        }
    }
}
