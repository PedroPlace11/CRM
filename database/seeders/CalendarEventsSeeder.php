<?php

namespace Database\Seeders;

use App\Models\CalendarEvent;
use App\Models\Deal;
use App\Models\Entity;
use App\Models\Person;
use App\Models\User;
use Illuminate\Database\Seeder;

class CalendarEventsSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::where('email', 'admin@crm.test')->value('id');

        $entityAurora = Entity::where('name', 'Aurora Tech Lda')->first();
        $entityBrisa = Entity::where('name', 'Brisa Consultoria')->first();
        $personMariana = Person::where('email', 'mariana.costa@auroratech.pt')->first();
        $personInes = Person::where('email', 'ines.almeida@brisaconsultoria.pt')->first();
        $dealAurora = Deal::where('title', 'Licencas CRM Aurora')->first();
        $dealBrisa = Deal::where('title', 'Consultoria Comercial Brisa')->first();

        $events = [
            [
                'title' => 'Reuniao de kickoff Aurora',
                'description' => 'Alinhar objetivos e plano de adocao do CRM.',
                'type' => 'meeting',
                'start_at' => now()->addDays(1)->setTime(10, 0),
                'end_at' => now()->addDays(1)->setTime(11, 0),
                'location' => 'Google Meet',
                'priority' => 'high',
                'completed' => false,
                'reminder_at' => now()->addDays(1)->setTime(9, 30),
                'eventable_type' => $entityAurora?->getMorphClass() ?? Entity::class,
                'eventable_id' => $entityAurora?->id,
            ],
            [
                'title' => 'Chamada com Mariana Costa',
                'description' => 'Revisar duvidas sobre proposta e proximo passo.',
                'type' => 'call',
                'start_at' => now()->addDays(2)->setTime(15, 30),
                'end_at' => now()->addDays(2)->setTime(16, 0),
                'location' => 'Telefone',
                'priority' => 'normal',
                'completed' => false,
                'reminder_at' => now()->addDays(2)->setTime(15, 0),
                'eventable_type' => $personMariana?->getMorphClass() ?? Person::class,
                'eventable_id' => $personMariana?->id,
            ],
            [
                'title' => 'Follow-up proposta Brisa',
                'description' => 'Confirmar retorno da proposta enviada.',
                'type' => 'task',
                'start_at' => now()->addDays(3)->setTime(9, 0),
                'end_at' => now()->addDays(3)->setTime(9, 30),
                'location' => null,
                'priority' => 'normal',
                'completed' => false,
                'reminder_at' => now()->addDays(3)->setTime(8, 30),
                'eventable_type' => $dealBrisa?->getMorphClass() ?? Deal::class,
                'eventable_id' => $dealBrisa?->id,
            ],
            [
                'title' => 'Demo executiva Brisa',
                'description' => 'Apresentacao para decisores da Brisa.',
                'type' => 'meeting',
                'start_at' => now()->addDays(5)->setTime(14, 0),
                'end_at' => now()->addDays(5)->setTime(15, 0),
                'location' => 'Escritorio Brisa',
                'priority' => 'high',
                'completed' => false,
                'reminder_at' => now()->addDays(5)->setTime(13, 30),
                'eventable_type' => $entityBrisa?->getMorphClass() ?? Entity::class,
                'eventable_id' => $entityBrisa?->id,
            ],
            [
                'title' => 'Nota interna negocio Aurora',
                'description' => 'Atualizar previsao de fecho apos reuniao tecnica.',
                'type' => 'note',
                'start_at' => now()->addDays(6)->setTime(17, 0),
                'end_at' => now()->addDays(6)->setTime(17, 20),
                'location' => null,
                'priority' => 'low',
                'completed' => false,
                'reminder_at' => null,
                'eventable_type' => $dealAurora?->getMorphClass() ?? Deal::class,
                'eventable_id' => $dealAurora?->id,
            ],
            [
                'title' => 'Chamada com Ines Almeida',
                'description' => 'Fechar pontos pendentes do contrato.',
                'type' => 'call',
                'start_at' => now()->addDays(7)->setTime(11, 0),
                'end_at' => now()->addDays(7)->setTime(11, 30),
                'location' => 'Teams',
                'priority' => 'normal',
                'completed' => false,
                'reminder_at' => now()->addDays(7)->setTime(10, 45),
                'eventable_type' => $personInes?->getMorphClass() ?? Person::class,
                'eventable_id' => $personInes?->id,
            ],
        ];

        foreach ($events as $event) {
            CalendarEvent::updateOrCreate(
                ['title' => $event['title']],
                array_merge($event, ['owner_id' => $ownerId])
            );
        }
    }
}
