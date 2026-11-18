@php
    $variantLabels = collect($variants)->mapWithKeys(fn($variant) => [$variant->id => $variant->code.' · '.$variant->model.' / '.$variant->color.' / '.$variant->size]);
    $orderLabels = collect($orders)->mapWithKeys(fn($order) => [$order->id => $order->number.' · '.($dispatchReport['customers'][$order->customer_id]['name'] ?? 'Cliente #'.$order->customer_id)]);
    $savedToken = old('operation_id');
    $dispatchToken = is_string($savedToken) && \Illuminate\Support\Str::isUuid($savedToken) ? $savedToken : $operationId;
@endphp
<x-filament::section heading="Confirmar despacho o retiro" class="simon-section">
    <p>Confirmá cada artículo y lote de esta entrega. El pedido queda Parcial mientras tenga pares por entregar y pasa a Entregado cuando se completa el saldo de todos sus artículos.</p>
    @if($orders === [])<p role="status">No hay pedidos con saldo que coincidan con la búsqueda.</p>@endif
    <form class="simon-operation-form" method="post" action="{{ route('operations.dispatch') }}" data-address-selector data-empty-address-label="Seleccionar destino antes de despachar">
        @csrf
        <x-simon::input type="hidden" name="operation_id" :value="$dispatchToken" />
        <label>Línea del pedido<x-simon::select name="order_line_id" required data-address-source>
            <option value="">Seleccionar pedido y artículo</option>
            @foreach($lines as $line)
                @if($line->pending > 0)
                    <option value="{{ $line->id }}" @selected((string) old('order_line_id') === (string) $line->id) data-address-url="{{ route('customers.addresses.options', $line->customer_id) }}" data-destination-label="{{ $line->delivery_destination === null ? 'Seleccionar destino antes de despachar' : 'Usar destino del pedido: '.$line->delivery_destination['label'].' · '.$line->delivery_destination['street'].' · '.$line->delivery_destination['locality'].' / '.$line->delivery_destination['province'] }}">{{ $orderLabels[$line->order_id] }} · {{ $variantLabels[$line->variant_id] ?? '#'.$line->variant_id }} · {{ $line->pending }} pares pendientes</option>
                @endif
            @endforeach
        </x-simon::select></label>
        <label>Destino de esta entrega<x-simon::select name="delivery_address_id" data-selected-address="{{ old('delivery_address_id', '') }}"><option value="">Seleccionar destino antes de despachar</option></x-simon::select></label>
        <p data-address-status role="status" aria-live="polite"></p>
        <p>Podés usar la copia del destino del pedido o elegir otra dirección activa del mismo cliente. El destino queda registrado para esta entrega.</p>
        <label>Lote de depósito<x-simon::select name="batch_id" required>
            <option value="">Seleccionar lote del artículo</option>
            @foreach($stock as $batch)
                <option value="{{ $batch->id }}" @selected((string) old('batch_id') === (string) $batch->id)>{{ $variantLabels[$batch->variant_id] ?? '#'.$batch->variant_id }} · {{ $batch->lot }} · {{ $batch->manufactured_on }} · {{ $batch->quantity }} pares</option>
            @endforeach
        </x-simon::select></label>
        @if($stock === [])<p role="status">No hay lotes con stock para estos artículos y filtros. Revisá el lote buscado o registrá el ingreso correspondiente.</p>@endif
        <label>Pares que salen<x-simon::input name="quantity" type="number" required min="1" max="1000000" :value="old('quantity')" /></label>
        <label><x-simon::input type="checkbox" name="owner_withdrawal" value="1" :checked="(bool) old('owner_withdrawal', false)" /> Retiro de propietarios</label>
        <p>Requiere validación administrativa. El despacho no registra cobros ni cancela deuda monetaria.</p>
        <x-filament::button type="submit" icon="heroicon-o-truck">Confirmar salida y actualizar pares pendientes</x-filament::button>
    </form>
</x-filament::section>
