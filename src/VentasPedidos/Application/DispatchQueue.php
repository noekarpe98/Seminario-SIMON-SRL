<?php

namespace Simon\VentasPedidos\Application;

interface DispatchQueue
{
    /** @return array{orders:list<object>,lines:list<object>,page:int,pages:int,total:int} */
    public function pending(string $number, int $page): array;
}
