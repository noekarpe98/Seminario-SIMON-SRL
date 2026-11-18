@extends('shared::layout')
@section('title', $pageTitle)
@section('content')
@if($section === 'orders')
<x-filament::section heading="Consultar pedidos por origen" class="simon-section">
    <form method="get" action="{{ route('operations.orders') }}">
        <label>Origen del pedido
            <x-simon::select name="origin">
                <option value="">Todos los orígenes</option>
                @foreach($originLabels as $code => $label)
                    <option value="{{ $code }}" @selected($originFilter === $code)>{{ $label }}</option>
                @endforeach
            </x-simon::select>
        </label>
        <x-filament::button type="submit" icon="heroicon-o-funnel">Aplicar filtro</x-filament::button>
        @if($originFilter !== null)<x-filament::link href="{{ route('operations.orders') }}">Quitar filtro</x-filament::link>@endif
    </form>
</x-filament::section>
@endif
@if($dispatchReport !== null)
<x-filament::section heading="Buscar notas para entregar" class="simon-section">
    <form method="get" action="{{ route('operations.dispatch.index') }}">
        <label>Número de pedido<x-simon::input name="order_number" value="{{ $dispatchFilters['order_number'] ?? '' }}" maxlength="100" /></label>
        @if($section === 'dispatch')<label>Lote exacto<x-simon::input name="lot" value="{{ $dispatchFilters['lot'] ?? '' }}" maxlength="100" /></label>@endif
        <x-filament::button type="submit">Buscar y actualizar opciones</x-filament::button>
    </form>
    <p>Los selectores contienen las opciones de la página cargada. Los saldos se actualizan automáticamente; usá la búsqueda o los enlaces de página para cargar otras opciones. Cada salida vuelve a validar estado, saldo, lote y autorización.</p>
</x-filament::section>
@endif
@if($section === 'catalog')
<x-filament::section heading="Artículos registrados" class="simon-section"><div class="simon-table"><table><thead><tr><th>Código</th><th>Modelo</th><th>Color</th><th>Talle</th></tr></thead><tbody>
@forelse($variants as $variant)<tr><td>{{ $variant->code }}</td><td>{{ $variant->model }}</td><td>{{ $variant->color }}</td><td>{{ $variant->size }}</td></tr>@empty<tr><td colspan="4">Todavía no hay artículos registrados.</td></tr>@endforelse
</tbody></table></div></x-filament::section>
@endif
@if($section !== 'authorization')
<p>Usá el menú lateral para acceder a cada módulo. Los movimientos de depósito conservan lote y fecha de fabricación.</p><p id="operations-sync" role="status" aria-live="polite"></p>@livewire(\Simon\Shared\UI\LiveBalances::class, ['kind' => 'operations', 'parameters' => [...$dispatchFilters, 'section' => $section, 'origin' => $originFilter, 'article_page' => $pendingProduction['articles']['page'] ?? null, 'needs_page' => $productionNeeds['page'] ?? request('needs_page')]])
@endif
@if($section === 'overview' && $permissions['clients.manage'])<x-filament::section class="simon-section"><x-slot name="heading"><span>Clientes e historial crediticio</span></x-slot><p><x-filament::link href="{{ route('customers.index') }}">Consultar y editar perfiles, contactos e historial de clientes</x-filament::link></p></x-filament::section>@endif
@if($section === 'catalog' && $permissions['catalog.manage'])<x-filament::section class="simon-section" collapsible :collapsed="false"><x-slot name="heading">Registrar artículo del catálogo existente</x-slot><form class="simon-operation-form" method="post" action="{{ route('operations.variant') }}" >@csrf<x-simon::input type="hidden" name="operation_id" value="{{ $operationId }}" /><div class="simon-grid"><label>Código<x-simon::input name="code" type="text" required maxlength="64" /></label><label>Modelo<x-simon::input name="model" type="text" required maxlength="100" /></label><label>Color<x-simon::input name="color" type="text" required maxlength="100" /></label><label>Talle<x-simon::input name="size" type="text" required maxlength="30" /></label></div><x-filament::button type="submit">Registrar artículo</x-filament::button></form></x-filament::section>@endif
@if($section === 'orders' && ($permissions['orders.create'] || $permissions['orders.manage']))
@include('ventas::order-form')
@endif
@if($section === 'authorization' && $permissions['dispatch.authorize'])
@livewire(\Simon\InventarioTrazabilidad\UI\DispatchAuthorizations::class)
@endif
@if($section === 'production' && $permissions['production.manage'])<x-filament::section class="simon-section" collapsible :collapsed="false"><x-slot name="heading">Registrar tanda en proceso</x-slot><form class="simon-operation-form" method="post" action="{{ route('operations.production') }}" >@csrf<x-simon::input type="hidden" name="operation_id" value="{{ $operationId }}" /><label>Artículo<x-simon::select name="variant_id" required>@foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->code }} · {{ $variant->model }} / {{ $variant->color }} / {{ $variant->size }}</option>@endforeach</x-simon::select></label><div class="simon-grid"><label>Lote de fábrica<x-simon::input name="lot" type="text" required maxlength="100" /></label><label>Fecha de fabricación<x-simon::input name="manufactured_on" type="date" required max="{{ date('Y-m-d') }}" /></label><label>Pares<x-simon::input name="quantity" type="number" required min="1" max="1000000" /></label></div><x-filament::button type="submit">Registrar tanda</x-filament::button></form></x-filament::section>@endif
@if($section === 'stock' && $permissions['inventory.receive'])<x-filament::section class="simon-section" collapsible :collapsed="false"><x-slot name="heading">Dar de alta productos terminados</x-slot><form class="simon-operation-form" method="post" action="{{ route('operations.receive') }}" >@csrf<x-simon::input type="hidden" name="operation_id" value="{{ $operationId }}" /><label>Tanda<x-simon::select name="production_id" required>@foreach($production as $batch)<option value="{{ $batch->id }}">#{{ $batch->id }} · {{ $batch->lot }} · {{ $batch->manufactured_on }} · {{ $batch->in_process }} pares en proceso</option>@endforeach</x-simon::select></label><label>Pares terminados<x-simon::input name="quantity" type="number" required min="1" max="1000000" /></label><x-filament::button type="submit">Confirmar alta en depósito</x-filament::button></form></x-filament::section>@endif
@if($section === 'stock' && $permissions['inventory.opening'])<x-filament::section class="simon-section" collapsible :collapsed="false"><x-slot name="heading">Cargar existencias iniciales verificadas</x-slot><form class="simon-operation-form" method="post" action="{{ route('operations.opening') }}" >@csrf<x-simon::input type="hidden" name="operation_id" value="{{ $operationId }}" /><label>Artículo<x-simon::select name="variant_id" required>@foreach($variants as $variant)<option value="{{ $variant->id }}">{{ $variant->code }} · {{ $variant->model }} / {{ $variant->color }} / {{ $variant->size }}</option>@endforeach</x-simon::select></label><div class="simon-grid"><label>Lote de fábrica<x-simon::input name="lot" type="text" required maxlength="100" /></label><label>Fecha de fabricación<x-simon::input name="manufactured_on" type="date" required max="{{ date('Y-m-d') }}" /></label><label>Pares<x-simon::input name="quantity" type="number" required min="1" max="1000000" /></label></div><x-filament::button type="submit">Confirmar stock inicial</x-filament::button></form></x-filament::section>@endif
@if($section === 'dispatch' && $permissions['inventory.dispatch'])
@include('inventario::dispatch-form')
@endif
@if($section === 'audit' && $permissions['audit.view'])<x-filament::section class="simon-section" collapsible :collapsed="false"><x-slot name="heading">Historial de movimientos</x-slot><div class="simon-table"><table><thead><tr><th>Secuencia</th><th>Fecha y hora UTC</th><th>Usuario</th><th>Movimiento</th><th>Pares</th><th>Lote / fabricación</th><th>Destino de la entrega</th></tr></thead><tbody>@foreach($audits as $audit)@php($entry = json_decode($audit->payload, true))<tr><td>{{ $audit->sequence }}</td><td>{{ $entry['occurred_at'] }}</td><td>{{ $entry['actor_name'] }}</td><td>{{ ['receipt'=>'Alta de terminados','opening'=>'Stock inicial','dispatch'=>'Despacho','owner_withdrawal'=>'Retiro de propietarios'][$entry['kind']] ?? $entry['kind'] }}</td><td>{{ $entry['quantity'] }}</td><td>{{ $entry['batch']['lot'] }} / {{ $entry['batch']['manufactured_on'] }}</td><td>@include('clientes::destination', ['destination' => $entry['delivery_destination'] ?? null])</td></tr>@endforeach</tbody></table></div></x-filament::section>@endif
@endsection
