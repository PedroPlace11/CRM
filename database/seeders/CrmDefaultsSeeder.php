<?php

namespace Database\Seeders;

use App\Models\DealStage;
use App\Models\FollowUpTemplate;
use Illuminate\Database\Seeder;

class CrmDefaultsSeeder extends Seeder
{
    public function run(): void
    {
        $stages = [
            ['name' => 'Lead',        'slug' => 'lead',        'position' => 1, 'color' => '#94a3b8'],
            ['name' => 'Proposta',    'slug' => 'proposta',    'position' => 2, 'color' => '#60a5fa'],
            ['name' => 'Negociação',  'slug' => 'negociacao',  'position' => 3, 'color' => '#fbbf24'],
            ['name' => 'Follow Up',   'slug' => 'follow-up',   'position' => 4, 'color' => '#a78bfa', 'is_follow_up' => true],
            ['name' => 'Ganho',       'slug' => 'ganho',       'position' => 5, 'color' => '#34d399', 'is_won' => true],
            ['name' => 'Perdido',     'slug' => 'perdido',     'position' => 6, 'color' => '#f87171', 'is_lost' => true],
        ];
        foreach ($stages as $s) DealStage::firstOrCreate(['slug' => $s['slug']], $s);

        $templates = [
            ['Olá! Tudo bem?',
             'Olá,\n\nApenas a confirmar se já teve oportunidade de analisar a proposta que enviámos. Fico ao dispor para qualquer dúvida.\n\nCumprimentos.'],
            ['Novidades sobre a proposta?',
             'Olá,\n\nGostaria de saber se há novidades em relação à proposta enviada. Disponível para esclarecer qualquer ponto.\n\nObrigado.'],
            ['Aqui para ajudar',
             'Olá,\n\nLembrei-me de si e quis saber se existe algo em que possamos ajudar relativamente à nossa proposta. Estou disponível.\n\nCumprimentos.'],
            ['Posso esclarecer alguma dúvida?',
             'Olá,\n\nSurgiu alguma dúvida com a proposta? Posso explicar qualquer ponto via chamada rápida.\n\nObrigado.'],
            ['Vamos falar 5 minutos?',
             'Olá,\n\nO que acha de marcarmos 5 minutos esta semana para alinhar próximos passos?\n\nCumprimentos.'],
            ['Atualização rápida',
             'Olá,\n\nQuis confirmar consigo se o timing continua apropriado e se posso ajudar a destrancar algo.\n\nObrigado.'],
            ['Algum bloqueio?',
             'Olá,\n\nExiste algum bloqueio do vosso lado em que possa ajudar? Disponível para apoiar.\n\nCumprimentos.'],
            ['Continua a fazer sentido?',
             'Olá,\n\nFaz ainda sentido avançarmos com esta proposta? Diga-me e ajustamos.\n\nObrigado.'],
            ['Quer rever a proposta?',
             'Olá,\n\nSe preferir, podemos rever os termos da proposta em conjunto. Diga-me a melhor altura.\n\nCumprimentos.'],
            ['Última verificação',
             'Olá,\n\nÚltima verificação da minha parte: se preferir que o contacte mais tarde, basta dizer.\n\nObrigado.'],
        ];
        foreach ($templates as [$subject, $body]) {
            FollowUpTemplate::firstOrCreate(['name' => $subject], [
                'subject' => $subject,
                'body'    => $body,
                'active'  => true,
            ]);
        }
    }
}
