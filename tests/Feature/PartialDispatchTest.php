<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Simon\Acceso\Infrastructure\Persistence\User;
use Simon\ClientesAdministracion\Application\ManageAddresses;
use Simon\ClientesAdministracion\Application\ManageCustomers;
use Simon\InventarioTrazabilidad\Application\MoveStock;
use Simon\InventarioTrazabilidad\Application\PrepareDispatch;
use Simon\InventarioTrazabilidad\Application\PrepareInventory;
use Simon\InventarioTrazabilidad\Infrastructure\SqlInventory;
use Simon\Shared\Domain\BusinessRuleViolation;
use Simon\Shared\Domain\PermissionDenied;
use Simon\VentasPedidos\Application\RegisterOrder;
use Tests\TestCase;

class PartialDispatchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private int $customer;

    private int $variant;

    private int $address;

    protected function setUp(): void
    {
        parent::setUp();
        config(['inventory.audit_key' => str_repeat('partial-dispatch-test-', 3)]);
        $this->admin = User::factory()->create();
        $this->artisan('access:bootstrap', ['email' => $this->admin->email])->assertSuccessful();
        $this->customer = app(ManageCustomers::class)->register($this->admin->id, 'Cliente de entregas', '20123456789', 'CRÉDITO PRIVADO');
        $this->variant = app(PrepareInventory::class)->variant($this->admin->id, 'ENT-01', 'Botín', 'Negro', '40');
        $this->address = app(ManageAddresses::class)->save($this->admin->id, $this->customer, null, 'Central', 'Calle Uno', 'Posadas', 'Misiones');
    }

    private function order(int $quantity = 10, ?array $lines = null): int
    {
        return app(RegisterOrder::class)->handle($this->admin->id, (string) Str::uuid(), $this->customer, 'F', $lines ?? [['variant_id' => $this->variant, 'quantity' => $quantity]], $this->address);
    }

    private function opening(int $variant, string $lot, int $quantity): int
    {
        $id = app(MoveStock::class)->opening($this->admin->id, (string) Str::uuid(), $variant, $lot, '2026-01-01', $quantity);

        return DB::table('inventory_movements')->where('id', $id)->value('batch_id');
    }

    public function test_old_pending_orders_remain_selectable_for_dispatch_and_administrative_authorization(): void
    {
        $order = $this->order();
        for ($index = 0; $index < 205; $index++) {
            DB::table('business_orders')->insert([
                'registration_id' => (string) Str::uuid(), 'request_digest' => str_repeat('a', 64), 'number' => 'TERMINADO-'.$index,
                'customer_id' => $this->customer, 'seller_id' => $this->admin->id, 'origin' => 'F', 'status' => 'Entregado', 'created_at' => now(),
            ]);
        }
        foreach (['operations.dispatch.index', 'operations.authorization'] as $route) {
            $this->actingAs($this->admin)->get(route($route))->assertOk()->assertSee('NP-000001')->assertDontSee('TERMINADO-204');
        }
        $this->post(route('operations.authorize'), ['order_id' => $order, 'billing' => 1, 'credit' => 1, 'conditions' => 'Entrega autorizada'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('dispatch_authorizations', ['order_id' => $order, 'billing_validated' => 1, 'credit_validated' => 1]);
    }

    public function test_queue_pagination_search_and_stock_include_records_after_the_old_500_limit(): void
    {
        for ($index = 1; $index <= 27; $index++) {
            $this->order($index);
        }
        $rows = [];
        for ($index = 1; $index <= 501; $index++) {
            $rows[] = ['variant_id' => $this->variant, 'lot' => 'LOTE-'.$index, 'manufactured_on' => '2026-01-01', 'quantity' => 5];
        }
        DB::table('inventory_batches')->insert($rows);
        $report = app(PrepareDispatch::class)->handle($this->admin->id, '', 2, '', 21);
        $this->assertSame(27, $report['total']);
        $this->assertCount(2, $report['orders']);
        $this->assertSame('NP-000026', $report['orders'][0]->number);
        $this->assertSame(501, $report['stockPage']['total']);
        $this->assertSame('LOTE-501', $report['stockPage']['items'][0]->lot);
        $this->actingAs($this->admin)->get(route('operations.dispatch.index', ['dispatch_page' => 2, 'stock_page' => 21]))
            ->assertOk()->assertSee('NP-000026')->assertSee('LOTE-501')->assertSee('dispatch_page=2')->assertSee('stock_page=21')->assertDontSee('CRÉDITO PRIVADO');
        $filtered = app(PrepareDispatch::class)->handle($this->admin->id, 'NP-000001', 1, 'LOTE-501');
        $this->assertCount(1, $filtered['orders']);
        $this->assertSame('LOTE-501', $filtered['stockPage']['items'][0]->lot);
        $this->assertCount(0, app(PrepareDispatch::class)->handle($this->admin->id, '%_')['orders']);
        $this->get(route('operations.snapshot', ['section' => 'dispatch', 'order_number' => 'NP-000001', 'lot' => 'LOTE-501']))
            ->assertOk()->assertSee('NP-000001')->assertSee('LOTE-501')->assertDontSee('<form', false);
    }

    public function test_dispatch_options_resolve_article_names_beyond_the_catalog_limit(): void
    {
        $rows = [];
        for ($index = 2; $index <= 501; $index++) {
            $rows[] = ['code' => 'ART-'.$index, 'model' => 'Modelo-'.$index, 'color' => 'Azul', 'size' => '40'];
        }
        DB::table('catalog_variants')->insert($rows);
        $variant = DB::table('catalog_variants')->where('code', 'ART-501')->value('id');
        $this->order(lines: [['variant_id' => $variant, 'quantity' => 10]]);
        $this->opening($variant, 'ESPECIAL%_', 10);
        $this->actingAs($this->admin)->get(route('operations.dispatch.index', ['lot' => 'ESPECIAL%_']))
            ->assertOk()->assertSee('Modelo-501')->assertSee('ESPECIAL%_');
    }

    public function test_multi_article_deliveries_keep_independent_destinations_and_only_finish_after_all_lines(): void
    {
        $other = app(PrepareInventory::class)->variant($this->admin->id, 'ENT-02', 'Zapato', 'Marrón', '42');
        $order = $this->order(lines: [['variant_id' => $this->variant, 'quantity' => 10], ['variant_id' => $other, 'quantity' => 3]]);
        app(ManageCustomers::class)->authorize($this->admin->id, $order, true, true, 'Entrega por artículo');
        $alternate = app(ManageAddresses::class)->save($this->admin->id, $this->customer, null, 'Sucursal', 'Calle Dos', 'Oberá', 'Misiones');
        $firstBatch = $this->opening($this->variant, 'LOTE-A', 4);
        $secondBatch = $this->opening($this->variant, 'LOTE-B', 6);
        $otherBatch = $this->opening($other, 'LOTE-C', 3);
        $lines = DB::table('business_order_lines')->where('order_id', $order)->orderBy('id')->pluck('id')->all();
        $this->actingAs($this->admin);
        $token = (string) Str::uuid();
        $request = ['operation_id' => $token, 'order_line_id' => $lines[0], 'batch_id' => $firstBatch, 'quantity' => 4];
        $this->post(route('operations.dispatch'), $request)->assertSessionHasNoErrors();
        $this->post(route('operations.dispatch'), $request)->assertSessionHasNoErrors();
        $this->assertDatabaseHas('business_order_lines', ['id' => $lines[0], 'dispatched' => 4]);
        $this->assertDatabaseHas('business_orders', ['id' => $order, 'status' => 'Parcial']);
        $this->post(route('operations.dispatch'), ['operation_id' => (string) Str::uuid(), 'order_line_id' => $lines[0], 'batch_id' => $secondBatch, 'quantity' => 6, 'delivery_address_id' => $alternate])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('business_orders', ['id' => $order, 'status' => 'Parcial']);
        $this->post(route('operations.dispatch'), ['operation_id' => (string) Str::uuid(), 'order_line_id' => $lines[1], 'batch_id' => $otherBatch, 'quantity' => 3])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('business_orders', ['id' => $order, 'status' => 'Entregado', 'revision' => 3]);
        $this->assertSame(0, app(PrepareDispatch::class)->handle($this->admin->id)['total']);
        $entries = DB::table('inventory_audits')->orderBy('sequence')->get()->map(fn (object $entry): array => json_decode($entry->payload, true))->filter(fn (array $entry): bool => $entry['kind'] === 'dispatch')->values();
        $this->assertSame(['Calle Uno', 'Calle Dos', 'Calle Uno'], $entries->pluck('delivery_destination.street')->all());
        $this->assertSame([4, 6, 3], $entries->pluck('quantity')->all());
        $this->assertSame(6, app(SqlInventory::class)->verify()['sequence']);
        $this->assertDatabaseHas('business_customers', ['id' => $this->customer, 'credit_history' => 'CRÉDITO PRIVADO']);
        $this->get(route('traceability.index', ['lot' => 'LOTE-B']))->assertOk()->assertSee('Calle Dos')->assertSee('NP-000001');
    }

    public function test_audit_failure_rolls_back_the_dispatch_and_keeps_the_form_for_correction(): void
    {
        $order = $this->order();
        app(ManageCustomers::class)->authorize($this->admin->id, $order, true, true, 'Prueba de reversión');
        $batch = $this->opening($this->variant, 'AUDIT', 10);
        $token = (string) Str::uuid();
        $line = DB::table('business_order_lines')->value('id');
        config(['inventory.audit_key' => null]);
        $this->actingAs($this->admin)->from(route('operations.dispatch.index'))->post(route('operations.dispatch'), [
            'operation_id' => $token, 'order_line_id' => $line, 'batch_id' => $batch, 'quantity' => 4, 'delivery_address_id' => $this->address,
        ])->assertSessionHasErrors('operation')->assertSessionHasInput('operation_id', $token)->assertSessionHasInput('quantity', 4);
        $this->assertDatabaseHas('business_order_lines', ['id' => $line, 'dispatched' => 0]);
        $this->assertDatabaseHas('business_orders', ['status' => 'Confirmado', 'revision' => 0]);
        $this->assertDatabaseHas('inventory_batches', ['id' => $batch, 'quantity' => 10]);
        $this->assertDatabaseCount('inventory_movements', 1);
        $this->get(route('operations.dispatch.index'))->assertOk()->assertSee($token)->assertSee('data-selected-address="'.$this->address.'"', false);
    }

    public function test_queue_permissions_are_checked_in_application_and_http(): void
    {
        $this->order();
        foreach (['inventory.dispatch', 'dispatch.authorize', 'orders.create', 'inventory.view', 'production.manage', 'inventory.receive'] as $permission) {
            $user = User::factory()->create();
            $role = DB::table('access_roles')->insertGetId(['name' => $permission]);
            DB::table('access_role_permissions')->insert(['role_id' => $role, 'permission' => $permission]);
            DB::table('access_user_roles')->insert(['user_id' => $user->id, 'role_id' => $role]);
            if (in_array($permission, ['inventory.dispatch', 'dispatch.authorize'], true)) {
                $this->assertSame(1, app(PrepareDispatch::class)->handle($user->id)['total']);
                $route = $permission === 'inventory.dispatch' ? 'operations.dispatch.index' : 'operations.authorization';
                $this->actingAs($user)->get(route($route))->assertOk()->assertDontSee('CRÉDITO PRIVADO');
            } else {
                try {
                    app(PrepareDispatch::class)->handle($user->id);
                    $this->fail('No debe permitir consultar la cola sin permiso.');
                } catch (PermissionDenied) {
                    $this->assertTrue(true);
                }
                $this->actingAs($user)->get(route('operations.dispatch.index'))->assertForbidden();
                $this->get(route('operations.snapshot', ['section' => 'dispatch']))->assertForbidden();
                $this->post(route('operations.dispatch'), [])->assertForbidden();
            }
        }
    }

    public function test_invalid_filters_are_rejected_and_empty_queues_are_explicit(): void
    {
        $this->actingAs($this->admin)->get(route('operations.dispatch.index'))->assertOk()->assertSee('No hay pedidos con saldo');
        foreach (['dispatch_page' => 0, 'stock_page' => 0, 'order_number' => str_repeat('a', 101), 'lot' => ['array']] as $field => $value) {
            $this->get(route('operations.dispatch.index', [$field => $value]))->assertSessionHasErrors($field);
        }
        $this->expectException(BusinessRuleViolation::class);
        app(PrepareDispatch::class)->handle($this->admin->id, orderPage: 0);
    }
}
