<?php

namespace Database\Seeders;

use App\Models\Deal;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductsSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'name' => 'CRM Starter',
                'sku' => 'CRM-START-001',
                'unit_price' => 1200.00,
                'description' => 'Plano inicial do CRM para pequenas equipes.',
                'active' => true,
            ],
            [
                'name' => 'CRM Pro',
                'sku' => 'CRM-PRO-001',
                'unit_price' => 2900.00,
                'description' => 'Plano avancado com automacoes e funil completo.',
                'active' => true,
            ],
            [
                'name' => 'CRM Enterprise',
                'sku' => 'CRM-ENT-001',
                'unit_price' => 5600.00,
                'description' => 'Plano corporativo com recursos premium e suporte dedicado.',
                'active' => true,
            ],
            [
                'name' => 'Pacote Onboarding',
                'sku' => 'SERV-ONB-001',
                'unit_price' => 1500.00,
                'description' => 'Implantacao inicial, setup e treinamento da equipe.',
                'active' => true,
            ],
            [
                'name' => 'Consultoria Comercial',
                'sku' => 'SERV-CONS-001',
                'unit_price' => 2200.00,
                'description' => 'Consultoria de processos comerciais e performance.',
                'active' => true,
            ],
            [
                'name' => 'Integracao ERP',
                'sku' => 'SERV-ERP-001',
                'unit_price' => 3400.00,
                'description' => 'Integracao entre CRM e sistema ERP do cliente.',
                'active' => true,
            ],
        ];

        $savedProducts = collect($products)->mapWithKeys(function (array $data) {
            $product = Product::updateOrCreate(
                ['sku' => $data['sku']],
                $data
            );

            return [$product->name => $product];
        });

        $dealProductMap = [
            'Licencas CRM Aurora' => [
                ['product' => 'CRM Pro', 'quantity' => 4, 'unit_price' => 2800.00, 'discount' => 5.00],
                ['product' => 'Pacote Onboarding', 'quantity' => 1, 'unit_price' => 1500.00, 'discount' => 0.00],
            ],
            'Consultoria Comercial Brisa' => [
                ['product' => 'Consultoria Comercial', 'quantity' => 3, 'unit_price' => 2100.00, 'discount' => 0.00],
                ['product' => 'CRM Starter', 'quantity' => 2, 'unit_price' => 1200.00, 'discount' => 0.00],
            ],
            'Renovacao Retail Cobalto' => [
                ['product' => 'CRM Enterprise', 'quantity' => 2, 'unit_price' => 5400.00, 'discount' => 8.00],
                ['product' => 'Integracao ERP', 'quantity' => 1, 'unit_price' => 3300.00, 'discount' => 0.00],
            ],
            'Pacote Marketing Estrela' => [
                ['product' => 'CRM Starter', 'quantity' => 3, 'unit_price' => 1150.00, 'discount' => 3.00],
                ['product' => 'Pacote Onboarding', 'quantity' => 1, 'unit_price' => 1500.00, 'discount' => 0.00],
            ],
            'Follow up Delta Obras' => [
                ['product' => 'CRM Pro', 'quantity' => 2, 'unit_price' => 2850.00, 'discount' => 4.00],
                ['product' => 'Consultoria Comercial', 'quantity' => 1, 'unit_price' => 2200.00, 'discount' => 0.00],
            ],
        ];

        foreach ($dealProductMap as $dealTitle => $rows) {
            $deal = Deal::where('title', $dealTitle)->first();

            if (! $deal) {
                continue;
            }

            $syncData = [];

            foreach ($rows as $row) {
                $product = $savedProducts->get($row['product']);

                if (! $product) {
                    continue;
                }

                $syncData[$product->id] = [
                    'quantity' => $row['quantity'],
                    'unit_price' => $row['unit_price'],
                    'discount' => $row['discount'],
                ];
            }

            if (! empty($syncData)) {
                $deal->products()->syncWithoutDetaching($syncData);
            }
        }
    }
}
