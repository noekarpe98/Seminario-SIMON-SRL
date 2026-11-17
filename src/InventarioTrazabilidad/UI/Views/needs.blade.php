<x-filament::section heading="Necesidades de producción" class="simon-section">
    <p>Stock físico disponible + pares en proceso − pares pendientes de entrega. Un saldo negativo indica los pares que faltan por producir o inyectar para cubrir los pedidos. Esta proyección no modifica el stock ni registra una orden de producción.</p>
    <div class="simon-table"><table>
        <thead><tr><th>Artículo</th><th>Disponible</th><th>En proceso</th><th>Pendientes</th><th>Saldo proyectado</th><th>Pares faltantes</th></tr></thead>
        <tbody>
        @forelse($needsReport['items'] as $item)
            <tr data-needs-variant="{{ $item['id'] }}" data-needs-balance="{{ $item['balance'] }}">
                <td>{{ $item['code'] }} · {{ $item['model'] }} / {{ $item['color'] }} / {{ $item['size'] }}</td>
                <td>{{ $item['available'] }}</td><td>{{ $item['in_process'] }}</td><td>{{ $item['pending'] }}</td>
                <td>{{ $item['balance'] }}</td><td>{{ $item['needed'] }}</td>
            </tr>
        @empty
            <tr><td colspan="6">No hay artículos registrados para calcular necesidades.</td></tr>
        @endforelse
        </tbody>
    </table></div>
    <nav aria-label="Páginas de necesidades">
        <p>{{ $needsReport['total'] }} artículos · página {{ $needsReport['page'] }} de {{ $needsReport['pages'] }}.</p>
        @if($needsReport['page'] > 1)
            <x-filament::link :href="route('operations.production.index', ['needs_page' => $needsReport['page'] - 1, 'article_page' => $pendingProduction['articles']['page']])">Necesidades anteriores</x-filament::link>
        @endif
        @if($needsReport['page'] < $needsReport['pages'])
            <x-filament::link :href="route('operations.production.index', ['needs_page' => $needsReport['page'] + 1, 'article_page' => $pendingProduction['articles']['page']])">Necesidades siguientes</x-filament::link>
        @endif
    </nav>
</x-filament::section>
