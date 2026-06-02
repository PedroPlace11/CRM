<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\DealStage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductStatsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        $stats = $this->buildStats($request);

        return Inertia::render('products/Stats', [
            'stats'   => $stats,
            'filters' => $request->only(['from', 'to', 'stage_id', 'owner_id']),
            'stages'  => DealStage::orderBy('position')->get(['id', 'name']),
            'owners'  => $isAdmin
                ? User::orderBy('name')->get(['id', 'name'])
                : User::where('id', $user->id)->get(['id', 'name']),
        ]);
    }

    public function show(Request $request, Product $product)
    {
        $user = $request->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        $deals = Deal::query()
            ->whereHas('products', fn ($q) => $q->where('products.id', $product->id))
            ->when(! $isAdmin, fn ($q) => $q->where('owner_id', $user->id))
            ->with(['stage:id,name', 'entity:id,name', 'owner:id,name', 'products' => fn ($q) =>
                $q->where('products.id', $product->id)])
            ->latest()
            ->get();

        return Inertia::render('products/Show', [
            'product' => $product,
            'deals'   => $deals,
        ]);
    }

    public function export(Request $request)
    {
        $stats = $this->buildStats($request);

        return Excel::download(new class($stats) implements FromArray, WithHeadings {
            public function __construct(private array $stats) {}
            public function array(): array
            {
                return array_map(fn ($r) => [$r['name'], $r['quantity'], $r['total_value']], $this->stats);
            }
            public function headings(): array
            {
                return ['Produto', 'Quantidade', 'Valor Total'];
            }
        }, 'product-stats.xlsx');
    }

    private function buildStats(Request $request): array
    {
        $user = $request->user();
        $isAdmin = $user->hasAnyRole(['admin', 'manager']);
        $rows = Product::query()
            ->select('products.id', 'products.name')
            ->selectRaw('COALESCE(SUM(deal_products.quantity),0) AS quantity')
            ->selectRaw('COALESCE(SUM(deal_products.quantity * deal_products.unit_price * (1 - deal_products.discount/100)),0) AS total_value')
            ->leftJoin('deal_products', 'deal_products.product_id', '=', 'products.id')
            ->leftJoin('deals', 'deals.id', '=', 'deal_products.deal_id')
            ->when($request->date('from'), fn ($q, $d) => $q->where('deals.created_at', '>=', $d))
            ->when($request->date('to'),   fn ($q, $d) => $q->where('deals.created_at', '<=', $d))
            ->when($request->integer('stage_id'), fn ($q, $id) => $q->where('deals.stage_id', $id))
            ->when(! $isAdmin, fn ($q) => $q->where('deals.owner_id', $user->id))
            ->when($isAdmin && $request->integer('owner_id'), fn ($q, $id) => $q->where('deals.owner_id', $id))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_value')
            ->get();

        return $rows->map(fn ($r) => [
            'id'          => $r->id,
            'name'        => $r->name,
            'quantity'    => (int) $r->quantity,
            'total_value' => (float) $r->total_value,
        ])->all();
    }
}
