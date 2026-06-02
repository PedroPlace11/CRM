<?php

namespace Database\Seeders;

use App\Models\Entity;
use App\Models\LeadSubmission;
use App\Models\Person;
use App\Models\PublicForm;
use App\Models\User;
use Illuminate\Database\Seeder;

class PublicFormsSeeder extends Seeder
{
    public function run(): void
    {
        $ownerId = User::where('email', 'admin@crm.test')->value('id');

        $forms = [
            [
                'name' => 'Pedido de contato comercial',
                'slug' => 'pedido-de-contato-comercial-demo',
                'success_message' => 'Obrigado! Nossa equipa comercial vai responder em breve.',
                'captcha_required' => true,
                'active' => true,
                'fields' => [
                    ['key' => 'name', 'label' => 'Nome', 'type' => 'text', 'required' => true],
                    ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['key' => 'phone', 'label' => 'Telefone', 'type' => 'tel', 'required' => false],
                    ['key' => 'message', 'label' => 'Mensagem', 'type' => 'textarea', 'required' => false],
                ],
            ],
            [
                'name' => 'Solicitacao de demonstracao',
                'slug' => 'solicitacao-de-demonstracao-demo',
                'success_message' => 'Recebemos seu pedido de demo. Entraremos em contacto para agendar.',
                'captcha_required' => true,
                'active' => true,
                'fields' => [
                    ['key' => 'name', 'label' => 'Nome', 'type' => 'text', 'required' => true],
                    ['key' => 'email', 'label' => 'Email corporativo', 'type' => 'email', 'required' => true],
                    ['key' => 'company', 'label' => 'Empresa', 'type' => 'text', 'required' => true],
                    ['key' => 'employees', 'label' => 'Numero de colaboradores', 'type' => 'number', 'required' => false],
                    ['key' => 'goal', 'label' => 'Objetivo principal', 'type' => 'textarea', 'required' => false],
                ],
            ],
            [
                'name' => 'Formulario para parceria',
                'slug' => 'formulario-para-parceria-demo',
                'success_message' => 'Pedido de parceria enviado com sucesso.',
                'captcha_required' => false,
                'active' => true,
                'fields' => [
                    ['key' => 'name', 'label' => 'Nome', 'type' => 'text', 'required' => true],
                    ['key' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['key' => 'company', 'label' => 'Empresa', 'type' => 'text', 'required' => true],
                    ['key' => 'proposal', 'label' => 'Proposta de parceria', 'type' => 'textarea', 'required' => true],
                ],
            ],
        ];

        $savedForms = collect($forms)->mapWithKeys(function (array $data) use ($ownerId) {
            $form = PublicForm::updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'name' => $data['name'],
                    'fields' => $data['fields'],
                    'success_message' => $data['success_message'],
                    'captcha_required' => $data['captcha_required'],
                    'owner_id' => $ownerId,
                    'active' => $data['active'],
                ]
            );

            return [$form->slug => $form];
        });

        $people = Person::query()->orderBy('id')->get(['id', 'name', 'email', 'phone', 'entity_id']);
        $entities = Entity::query()->orderBy('id')->get(['id', 'name']);

        $submissionsByForm = [
            'pedido-de-contato-comercial-demo' => [
                [
                    'person_email' => 'mariana.costa@auroratech.pt',
                    'source_ip' => '203.0.113.11',
                    'payload' => [
                        'name' => 'Mariana Costa',
                        'email' => 'mariana.costa@auroratech.pt',
                        'phone' => '+351 910 000 101',
                        'message' => 'Gostaria de receber uma proposta para 20 utilizadores.',
                    ],
                ],
                [
                    'person_email' => 'ines.almeida@brisaconsultoria.pt',
                    'source_ip' => '203.0.113.12',
                    'payload' => [
                        'name' => 'Ines Almeida',
                        'email' => 'ines.almeida@brisaconsultoria.pt',
                        'phone' => '+351 910 000 201',
                        'message' => 'Preciso integrar CRM com fluxo comercial atual.',
                    ],
                ],
            ],
            'solicitacao-de-demonstracao-demo' => [
                [
                    'person_email' => 'pedro.lopes@estrelamedia.pt',
                    'source_ip' => '203.0.113.21',
                    'payload' => [
                        'name' => 'Pedro Lopes',
                        'email' => 'pedro.lopes@estrelamedia.pt',
                        'company' => 'Estrela Media',
                        'employees' => 35,
                        'goal' => 'Aumentar produtividade da equipa comercial.',
                    ],
                ],
                [
                    'person_email' => 'sara.carvalho@delta.pt',
                    'source_ip' => '203.0.113.22',
                    'payload' => [
                        'name' => 'Sara Carvalho',
                        'email' => 'sara.carvalho@delta.pt',
                        'company' => 'Delta Obras',
                        'employees' => 22,
                        'goal' => 'Controlar follow-up de propostas.',
                    ],
                ],
            ],
            'formulario-para-parceria-demo' => [
                [
                    'person_email' => 'rui.ferreira@auroratech.pt',
                    'source_ip' => '203.0.113.31',
                    'payload' => [
                        'name' => 'Rui Ferreira',
                        'email' => 'rui.ferreira@auroratech.pt',
                        'company' => 'Aurora Tech Lda',
                        'proposal' => 'Parceria para revenda e onboarding conjunto.',
                    ],
                ],
            ],
        ];

        foreach ($submissionsByForm as $slug => $rows) {
            $form = $savedForms->get($slug);

            if (! $form) {
                continue;
            }

            LeadSubmission::where('public_form_id', $form->id)->delete();

            foreach ($rows as $row) {
                $person = $people->firstWhere('email', $row['person_email']);
                $entityId = $person?->entity_id ?? $entities->first()?->id;

                LeadSubmission::create([
                    'public_form_id' => $form->id,
                    'person_id' => $person?->id,
                    'entity_id' => $entityId,
                    'payload' => $row['payload'],
                    'source_ip' => $row['source_ip'],
                    'user_agent' => 'Seeder/FakeFormLead',
                    'submitted_at' => now()->subDays(rand(1, 15)),
                ]);
            }
        }
    }
}
