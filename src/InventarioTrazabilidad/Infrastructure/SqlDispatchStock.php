<?php

namespace Simon\InventarioTrazabilidad\Infrastructure;

use Illuminate\Support\Facades\DB;
use Simon\InventarioTrazabilidad\Application\DispatchStock;

final class SqlDispatchStock implements DispatchStock
{
    public function available(array $variantIds, string $lot, int $page): array
    {
        return DB::transaction(function () use ($variantIds, $lot, $page): array {
            $query = DB::table('inventory_batches')->whereIn('variant_id', $variantIds)->where('quantity', '>', 0);
            if ($lot !== '') {
                $query->where('lot', $lot);
            }
            $total = $query->count();
            $pages = max(1, (int) ceil($total / 25));
            $page = min($page, $pages);

            return ['items' => $query->orderBy('id')->offset(($page - 1) * 25)->limit(25)->get()->all(), 'page' => $page, 'pages' => $pages, 'total' => $total];
        });
    }
}
