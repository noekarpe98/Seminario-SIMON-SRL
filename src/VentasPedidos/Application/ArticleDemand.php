<?php

namespace Simon\VentasPedidos\Application;

interface ArticleDemand
{
    /**
     * @param  list<int>  $variantIds
     * @return array<int,int> Quantities indexed by variant; missing entries mean zero, failures must throw.
     */
    public function quantities(array $variantIds): array;
}
