<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RestaurantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $user = \App\Models\User::factory()->create();
        $user->assignRole('Admin');
        $this->actingAs($user);
    }

    public function test_order_crud_search_and_server_calculated_price(): void
    {
        $menu = DB::table('menu_items')->first();
        $payload = ['customer_name' => 'Maria', 'menu_item_id' => $menu->id, 'table_id' => null, 'quantity' => 2, 'status' => 'Pending', 'total_amount' => 1];
        $id = $this->postJson('/api/orders', $payload)->assertCreated()->assertJsonPath('data.total_amount', (int) $menu->price * 2)->json('data.id');
        $this->getJson('/api/orders?search=Maria')->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.menu_name', $menu->name);
        $this->getJson('/api/orders?search=Nobody')->assertJsonCount(0, 'data');
        $this->putJson('/api/orders/'.$id, array_replace($payload, ['quantity' => 3, 'status' => 'Completed']))->assertOk();
        $this->assertDatabaseHas('orders', ['id' => $id, 'total_amount' => $menu->price * 3, 'status' => 'Completed']);
        $this->deleteJson('/api/orders/'.$id)->assertOk();
        $this->assertDatabaseMissing('orders', ['id' => $id]);
    }

    public function test_reservation_crud_and_validation(): void
    {
        $payload = ['customer_name' => 'Juan', 'phone' => '09123456789', 'table_id' => DB::table('restaurant_tables')->value('id'), 'reservation_date' => '2026-10-01', 'reservation_time' => '18:30', 'guests' => 2, 'status' => 'Confirmed'];
        $id = $this->postJson('/api/reservations', $payload)->assertCreated()->json('data.id');
        $this->getJson('/api/reservations?search=0912')->assertOk()->assertJsonCount(1, 'data');
        $this->putJson('/api/reservations/'.$id, array_replace($payload, ['guests' => 4]))->assertOk();
        $this->assertDatabaseHas('reservations', ['id' => $id, 'guests' => 4]);
        $this->postJson('/api/reservations', array_replace($payload, ['table_id' => 9999, 'reservation_date' => 'invalid', 'guests' => 0]))->assertUnprocessable()->assertJsonValidationErrors(['table_id', 'reservation_date', 'guests']);
        $this->deleteJson('/api/reservations/'.$id)->assertOk();
        $this->assertDatabaseMissing('reservations', ['id' => $id]);
    }

    public function test_inventory_crud_and_low_stock_dashboard(): void
    {
        $payload = ['item_name' => 'Rice', 'quantity' => 2.5, 'unit' => 'kg', 'reorder_level' => 3];
        $id = $this->postJson('/api/inventory', $payload)->assertCreated()->json('data.id');
        $this->getJson('/api/dashboard')->assertOk()->assertJsonPath('data.low_stock', 1)->assertJsonPath('data.inventory', 1);
        $this->getJson('/api/inventory?search=Rice')->assertJsonCount(1, 'data');
        $this->putJson('/api/inventory/'.$id, array_replace($payload, ['quantity' => 20]))->assertOk();
        $this->getJson('/api/dashboard')->assertJsonPath('data.low_stock', 0);
        $this->postJson('/api/inventory', array_replace($payload, ['quantity' => -1]))->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $this->deleteJson('/api/inventory/'.$id)->assertOk();
        $this->assertDatabaseMissing('inventory_items', ['id' => $id]);
    }

    public function test_invalid_orders_and_missing_records_are_rejected(): void
    {
        $this->postJson('/api/orders', ['customer_name' => '', 'menu_item_id' => 99999, 'table_id' => 9999, 'quantity' => 0, 'status' => 'Invalid'])->assertUnprocessable()->assertJsonValidationErrors(['customer_name', 'menu_item_id', 'table_id', 'quantity', 'status']);
        foreach (['orders', 'reservations', 'inventory'] as $module) {
            $this->putJson('/api/'.$module.'/99999', [])->assertNotFound();
            $this->deleteJson('/api/'.$module.'/99999')->assertNotFound();
        }
        $this->getJson('/api/unknown')->assertNotFound();
    }

    public function test_adoption_and_reseeding_preserve_existing_records(): void
    {
        DB::table('menu_items')->where('id', 1)->update(['name' => 'Original Menu', 'price' => 99]);
        $migration = require database_path('migrations/2026_09_25_000000_create_restaurant_tables.php');
        $migration->up();
        $this->seed();
        $this->assertDatabaseHas('menu_items', ['id' => 1, 'name' => 'Original Menu', 'price' => 99]);
        $this->getJson('/api/lookups')->assertOk()->assertJsonCount(4, 'data.menu')->assertJsonCount(4, 'data.tables');
    }
}
