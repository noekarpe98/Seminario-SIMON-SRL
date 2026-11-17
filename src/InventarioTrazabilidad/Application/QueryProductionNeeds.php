<?php

namespace Simon\InventarioTrazabilidad\Application;

use Simon\Shared\Application\AccessPolicy;
use Simon\Shared\Domain\BusinessRuleViolation;
use Simon\VentasPedidos\Application\ArticleDemand;

final readonly class QueryProductionNeeds
{
    public function __construct(private AccessPolicy $access, private NeedsInventory $inventory, private ArticleDemand $demand) {}

    /** @return array<string,mixed> */
    public function handle(int $actorId, int $page = 1): array
    {
        $this->access->require($actorId, $this->access->allows($actorId, 'production.manage') ? 'production.manage' : 'inventory.view');
        if ($page < 1) {
            throw new BusinessRuleViolation('La página de necesidades no es válida.');
        }

        return $this->inventory->snapshot(function () use ($page): array {
            $report = $this->inventory->page($page);
            $pending = $this->demand->quantities(array_column($report['items'], 'id'));
            $report['items'] = array_map(function (array $row) use ($pending): array {
                $pairs = $pending[$row['id']] ?? 0;
                $balance = $row['available'] + $row['in_process'] - $pairs;

                return [...$row, 'pending' => $pairs, 'balance' => $balance, 'needed' => max(0, -$balance)];
            }, $report['items']);

            return $report;
        });
    }
}
