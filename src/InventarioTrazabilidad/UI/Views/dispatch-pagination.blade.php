@php($queueRoute = $section === 'dispatch' ? 'operations.dispatch.index' : 'operations.authorization')
<x-filament::section heading="Pedidos con saldo para entregar" class="simon-section">
    <p>{{ $dispatchReport['total'] }} notas · página {{ $dispatchReport['page'] }} de {{ $dispatchReport['pages'] }}. Se incluyen todas las notas Confirmadas o Parciales con saldo, comenzando por las más antiguas.</p>
    <nav aria-label="Páginas de pedidos para entregar" class="flex flex-wrap gap-4">
        @if($dispatchReport['page'] > 1)<x-filament::link :href="route($queueRoute, [...$dispatchFilters, 'dispatch_page' => $dispatchReport['page'] - 1, 'stock_page' => 1])">Pedidos anteriores</x-filament::link>@endif
        @if($dispatchReport['page'] < $dispatchReport['pages'])<x-filament::link :href="route($queueRoute, [...$dispatchFilters, 'dispatch_page' => $dispatchReport['page'] + 1, 'stock_page' => 1])">Pedidos siguientes</x-filament::link>@endif
    </nav>
    @if($section === 'dispatch')
        <p>{{ $dispatchReport['stockPage']['total'] }} lotes disponibles para los artículos de estas notas · página {{ $dispatchReport['stockPage']['page'] }} de {{ $dispatchReport['stockPage']['pages'] }}.</p>
        <nav aria-label="Páginas de lotes para despachar" class="flex flex-wrap gap-4">
            @if($dispatchReport['stockPage']['page'] > 1)<x-filament::link :href="route($queueRoute, [...$dispatchFilters, 'stock_page' => $dispatchReport['stockPage']['page'] - 1])">Lotes anteriores</x-filament::link>@endif
            @if($dispatchReport['stockPage']['page'] < $dispatchReport['stockPage']['pages'])<x-filament::link :href="route($queueRoute, [...$dispatchFilters, 'stock_page' => $dispatchReport['stockPage']['page'] + 1])">Lotes siguientes</x-filament::link>@endif
        </nav>
    @endif
</x-filament::section>
