<?php

namespace Simon\InventarioTrazabilidad\Application;

use Simon\ClientesAdministracion\Application\CustomerDirectory;
use Simon\Shared\Application\AccessPolicy;
use Simon\Shared\Domain\BusinessRuleViolation;
use Simon\VentasPedidos\Application\DispatchQueue;

final readonly class PrepareDispatch
{
    public function __construct(private AccessPolicy $access, private DispatchQueue $orders, private DispatchStock $stock, private CatalogVariants $catalog, private CustomerDirectory $customers) {}

    /** @return array<string,mixed> */
    public function handle(int $actorId, string $number = '', int $orderPage = 1, string $lot = '', int $stockPage = 1): array
    {
        $canDispatch = $this->access->allows($actorId, 'inventory.dispatch');
        $this->access->require($actorId, $canDispatch ? 'inventory.dispatch' : 'dispatch.authorize');
        if ($orderPage < 1 || $stockPage < 1 || mb_strlen($number) > 100 || mb_strlen($lot) > 100) {
            throw new BusinessRuleViolation('Los filtros o las páginas de despacho no son válidos.');
        }
        $queue = $this->orders->pending(trim($number), $orderPage);
        $variantIds = array_values(array_unique(array_column($queue['lines'], 'variant_id')));
        $pendingVariants = array_values(array_unique(array_column(array_filter($queue['lines'], fn (object $line): bool => $line->pending > 0), 'variant_id')));
        $stock = $canDispatch ? $this->stock->available($pendingVariants, trim($lot), $stockPage) : ['items' => [], 'page' => 1, 'pages' => 1, 'total' => 0];

        return [
            ...$queue, 'stockPage' => $stock,
            'variants' => array_map(fn (array $row): object => (object) $row, $this->catalog->identified($variantIds)),
            'customers' => array_column($this->customers->identified(array_values(array_unique(array_column($queue['orders'], 'customer_id')))), null, 'id'),
        ];
    }
}
