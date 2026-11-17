<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use Simon\Acceso\Infrastructure\Persistence\User;
use Simon\ClientesAdministracion\Application\ManageCustomers;
use Simon\InventarioTrazabilidad\Application\MoveStock;
use Simon\InventarioTrazabilidad\Application\PrepareInventory;
use Simon\InventarioTrazabilidad\Application\QueryProductionNeeds;
use Simon\Shared\Domain\PermissionDenied;
use Simon\VentasPedidos\Application\ArticleDemand;
use Simon\VentasPedidos\Application\ManageOrders;
use Simon\VentasPedidos\Application\RegisterOrder;
use Tests\TestCase;

class ProductionNeedsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private int $variant;

    private int $customer;

    protected function setUp(): void
    {
        parent::setUp();
        config(['inventory.audit_key' => str_repeat('production-needs-test-', 3)]);
        $this->admin = User::factory()->create();
        $this->artisan('access:bootstrap', ['email' => $this->admin->email])->assertSuccessful();
        $this->variant = app(PrepareInventory::class)->variant($this->admin->id, 'NEEDS-01', 'Botín', 'Negro', '40');
        $this->customer = app(ManageCustomers::class)->register($this->admin->id, 'Cliente privado', '20123456789', 'Crédito secreto');
    }

    private function order(int $quantity, bool $draft = false): int
    {
        return app(RegisterOrder::class)->handle($this->admin->id, (string) Str::uuid(), $this->customer, 'F', [['variant_id' => $this->variant, 'quantity' => $quantity]], null, $draft);
    }

    /** @return array<string,mixed> */
    private function row(): array
    {
        return app(QueryProductionNeeds::class)->handle($this->admin->id)['items'][0];
    }

    public function test_projection_tracks_receipt_dispatch_retry_and_cancellation_without_writing_on_reads(): void
    {
        $stock = app(MoveStock::class);
        $stock->opening($this->admin->id, (string) Str::uuid(), $this->variant, 'INICIAL', '2026-01-01', 10);
        $production = app(PrepareInventory::class)->production($this->admin->id, (string) Str::uuid(), $this->variant, 'TANDA', '2026-01-01', 4);
        $order = $this->order(20);
        $before = DB::table('inventory_audit_head')->first();
        $row = $this->row();
        $this->assertSame([10, 4, 20, -6, 6], array_map(fn (string $key): int => $row[$key], ['available', 'in_process', 'pending', 'balance', 'needed']));
        $this->assertEquals($before, DB::table('inventory_audit_head')->first());
        $token = (string) Str::uuid();
        $stock->receive($this->admin->id, $token, $production, 4);
        $stock->receive($this->admin->id, $token, $production, 4);
        $row = $this->row();
        $this->assertSame([14, 0, -6], [$row['available'], $row['in_process'], $row['balance']]);
        app(ManageCustomers::class)->authorize($this->admin->id, $order, true, true, 'Prueba');
        $line = DB::table('business_order_lines')->where('order_id', $order)->value('id');
        $batch = DB::table('inventory_batches')->where('lot', 'INICIAL')->value('id');
        $token = (string) Str::uuid();
        $stock->dispatch($this->admin->id, $token, $line, $batch, 3, true);
        $stock->dispatch($this->admin->id, $token, $line, $batch, 3, true);
        $row = $this->row();
        $this->assertSame([11, 17, -6], [$row['available'], $row['pending'], $row['balance']]);
        app(ManageOrders::class)->transition($this->admin->id, (string) Str::uuid(), $order, 1, 'cancel', 'Fin de prueba');
        $this->assertSame(11, $this->row()['balance']);
        $this->assertSame(0, $this->row()['needed']);
    }

    public function test_all_lots_are_aggregated_without_multiplying_stock_and_only_active_orders_generate_demand(): void
    {
        foreach ([2, 3, 5] as $quantity) {
            app(MoveStock::class)->opening($this->admin->id, (string) Str::uuid(), $this->variant, 'L'.$quantity, '2026-01-01', $quantity);
            app(PrepareInventory::class)->production($this->admin->id, (string) Str::uuid(), $this->variant, 'P'.$quantity, '2026-01-01', $quantity);
        }
        $this->order(100, true);
        $cancelled = $this->order(99);
        app(ManageOrders::class)->transition($this->admin->id, (string) Str::uuid(), $cancelled, 0, 'cancel', 'Prueba');
        $this->order(20);
        $this->order(21);
        $row = $this->row();
        $this->assertSame([10, 10, 41, -21], [$row['available'], $row['in_process'], $row['pending'], $row['balance']]);
    }

    public function test_pagination_reaches_articles_beyond_500_and_empty_sources_are_real_zeroes(): void
    {
        $rows = [];
        for ($index = 2; $index <= 501; $index++) {
            $rows[] = ['code' => 'N'.$index, 'model' => 'M'.$index, 'color' => 'Negro', 'size' => '40'];
        }
        DB::table('catalog_variants')->insert($rows);
        $report = app(QueryProductionNeeds::class)->handle($this->admin->id, 21);
        $this->assertSame(501, $report['total']);
        $this->assertCount(1, $report['items']);
        $this->assertSame('N501', $report['items'][0]['code']);
        $this->assertSame(0, $report['items'][0]['balance']);
        $this->actingAs($this->admin)->get(route('operations.production.index', ['needs_page' => 21]))
            ->assertOk()->assertSee('N501')->assertSee('needs_page=21')->assertDontSee('Crédito secreto');
        $this->get(route('operations.snapshot', ['section' => 'production', 'needs_page' => 21]))
            ->assertOk()->assertSee('N501')->assertDontSee('<form', false);
    }

    public function test_production_and_inventory_readers_are_allowed_but_other_permissions_are_not(): void
    {
        foreach (['production.manage', 'inventory.view', 'inventory.receive', 'orders.create', 'clients.manage'] as $permission) {
            $user = User::factory()->create();
            $role = DB::table('access_roles')->insertGetId(['name' => $permission]);
            DB::table('access_role_permissions')->insert(['role_id' => $role, 'permission' => $permission]);
            DB::table('access_user_roles')->insert(['user_id' => $user->id, 'role_id' => $role]);
            if (in_array($permission, ['production.manage', 'inventory.view'], true)) {
                $this->assertCount(1, app(QueryProductionNeeds::class)->handle($user->id)['items']);
                $this->actingAs($user)->get(route('operations.production.index'))->assertOk()->assertSee('Necesidades de producción')->assertDontSee('Cliente privado')->assertDontSee('Crédito secreto');
            } else {
                try {
                    app(QueryProductionNeeds::class)->handle($user->id);
                    $this->fail('La consulta debe denegar permisos insuficientes.');
                } catch (PermissionDenied) {
                    $this->assertTrue(true);
                }
                $this->actingAs($user)->get(route('operations.snapshot', ['section' => 'production']))->assertDontSee('Necesidades de producción');
            }
        }
    }

    public function test_demand_failure_is_not_interpreted_as_zero(): void
    {
        $this->mock(ArticleDemand::class)->shouldReceive('quantities')->once()->andThrow(new RuntimeException('Fuente no disponible'));
        $this->expectException(RuntimeException::class);
        $this->row();
    }

    public function test_read_is_not_limited_to_recent_200_orders_and_does_not_issue_writes(): void
    {
        for ($index = 1; $index <= 205; $index++) {
            $order = DB::table('business_orders')->insertGetId([
                'registration_id' => (string) Str::uuid(), 'request_digest' => str_repeat('a', 64),
                'number' => 'TEST-'.$index, 'customer_id' => $this->customer, 'seller_id' => $this->admin->id,
                'origin' => 'F', 'status' => 'Confirmado', 'created_at' => now(),
            ]);
            DB::table('business_order_lines')->insert(['order_id' => $order, 'variant_id' => $this->variant, 'quantity' => 2, 'dispatched' => 0]);
        }
        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });
        $this->assertSame(410, $this->row()['pending']);
        foreach ($queries as $query) {
            $this->assertDoesNotMatchRegularExpression('/^\s*(insert|update|delete|replace|create|drop|alter)\b/i', $query);
        }
    }

    public function test_invalid_page_is_rejected_and_empty_catalog_is_rendered(): void
    {
        $this->actingAs($this->admin)->get(route('operations.production.index', ['needs_page' => 0]))->assertSessionHasErrors('needs_page');
        DB::table('catalog_variants')->delete();
        $this->get(route('operations.production.index'))->assertOk()->assertSee('No hay artículos registrados para calcular necesidades.');
    }
}
