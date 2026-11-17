<?php

namespace Simon\InventarioTrazabilidad\Application;

use Closure;

interface NeedsInventory
{
    /** @param Closure():array<string,mixed> $query
     * @return array<string,mixed>
     */
    public function snapshot(Closure $query): array;

    /** @return array{items:list<array{id:int,code:string,model:string,color:string,size:string,available:int,in_process:int}>,page:int,pages:int,total:int} */
    public function page(int $page): array;
}
