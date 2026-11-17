<?php

namespace Simon\VentasPedidos\Infrastructure;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Simon\VentasPedidos\Application\DispatchQueue;
use Simon\VentasPedidos\Application\OrderQueries;
use Simon\VentasPedidos\Domain\OrderStatus;

final readonly class SqlDispatchQueue implements DispatchQueue
{
    public function __construct(private OrderQueries $orders) {}

    public function pending(string $number, int $page): array
    {
        return DB::transaction(function () use ($number, $page): array {
            $query = DB::table('business_orders')->whereIn('status', [OrderStatus::Confirmed->value, OrderStatus::Partial->value])
                ->whereExists(function (Builder $lines): void {
                    $lines->selectRaw('1')->from('business_order_lines')->whereColumn('order_id', 'business_orders.id')->whereColumn('quantity', '>', 'dispatched');
                });
            if ($number !== '') {
                $term = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $number).'%';
                $query->whereRaw("number LIKE ? ESCAPE '!'", [$term]);
            }
            $total = $query->count();
            $pages = max(1, (int) ceil($total / 25));
            $page = min($page, $pages);
            $orders = $query->orderBy('id')->offset(($page - 1) * 25)->limit(25)->get()
                ->map(function (object $order): object {
                    $order->delivery_destination = $order->delivery_destination === null ? null : json_decode($order->delivery_destination, true, flags: JSON_THROW_ON_ERROR);

                    return $order;
                })->all();

            return ['orders' => $orders, 'lines' => $this->orders->lines(array_column($orders, 'id')), 'page' => $page, 'pages' => $pages, 'total' => $total];
        });
    }
}
