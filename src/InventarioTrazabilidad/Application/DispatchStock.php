<?php

namespace Simon\InventarioTrazabilidad\Application;

interface DispatchStock
{
    /**
     * @param  list<int>  $variantIds
     * @return array{items:list<object>,page:int,pages:int,total:int}
     */
    public function available(array $variantIds, string $lot, int $page): array;
}
