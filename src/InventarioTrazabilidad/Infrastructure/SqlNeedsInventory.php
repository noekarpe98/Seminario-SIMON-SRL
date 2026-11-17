<?php

namespace Simon\InventarioTrazabilidad\Infrastructure;

use Closure;
use Illuminate\Support\Facades\DB;
use Simon\InventarioTrazabilidad\Application\NeedsInventory;

final class SqlNeedsInventory implements NeedsInventory
{
    public function snapshot(Closure $query): array
    {
        return DB::transaction($query);
    }

    public function page(int $page): array
    {
        $total = DB::table('catalog_variants')->count();
        $pages = max(1, (int) ceil($total / 25));
        $page = min($page, $pages);
        $stock = DB::table('inventory_batches')->selectRaw('variant_id, SUM(quantity) as available')->groupBy('variant_id');
        $production = DB::table('production_batches')->selectRaw('variant_id, SUM(in_process) as in_process')->groupBy('variant_id');
        $items = DB::table('catalog_variants as variants')
            ->leftJoinSub($stock, 'stock', 'stock.variant_id', '=', 'variants.id')
            ->leftJoinSub($production, 'production', 'production.variant_id', '=', 'variants.id')
            ->select('variants.*')->selectRaw('COALESCE(stock.available, 0) as available, COALESCE(production.in_process, 0) as in_process')
            ->orderBy('variants.id')->offset(($page - 1) * 25)->limit(25)->get()
            ->map(fn (object $row): array => [...(array) $row, 'id' => (int) $row->id, 'available' => (int) $row->available, 'in_process' => (int) $row->in_process])->all();

        return ['items' => $items, 'page' => $page, 'pages' => $pages, 'total' => $total];
    }
}
