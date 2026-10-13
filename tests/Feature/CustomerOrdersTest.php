<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_only_see_their_own_orders_and_cannot_spoof_ownership_or_status(): void
    {
        $this->seed();
        $customer = User::factory()->create(['name' => 'Same Name']);
        $customer->assignRole('Customer');
        $other = User::factory()->create(['name' => 'Same Name']);
        $other->assignRole('Viewer');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $payload = ['menu_item_id' => DB::table('menu_items')->value('id'), 'quantity' => 2];
        $this->actingAs($other);
        $otherId = $this->postJson('/api/orders', $payload)->assertCreated()->json('data.id');
        $this->actingAs($customer);
        $id = $this->postJson('/api/orders', [...$payload, 'user_id' => $other->id, 'customer_name' => 'Spoofed', 'status' => 'Completed', 'total_amount' => 0])
            ->assertCreated()->assertJsonPath('data.user_id', $customer->id)->assertJsonPath('data.status', 'Pending')
            ->assertJsonPath('data.customer_name', 'Same Name')->json('data.id');
        $this->getJson('/api/orders')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $id);
        $this->getJson('/api/orders?search=Same')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $id);
        $this->getJson('/api/dashboard')->assertExactJson(['data' => ['orders' => 1, 'reservations' => 0]]);
        foreach (['inventory'] as $module) {
            $this->getJson('/api/'.$module)->assertForbidden();
            $this->postJson('/api/'.$module, [])->assertForbidden();
        }
        foreach ([$id, $otherId] as $orderId) {
            $this->putJson('/api/orders/'.$orderId, $payload)->assertForbidden();
            $this->deleteJson('/api/orders/'.$orderId)->assertForbidden();
        }
        // Legacy orders cannot be assigned to a customer by matching their name.
        DB::table('orders')->insert(['customer_name' => 'Same Name', 'menu_item_id' => $payload['menu_item_id'], 'quantity' => 1, 'total_amount' => 100, 'status' => 'Pending']);
        $this->getJson('/api/orders')->assertJsonCount(1, 'data');
        $this->actingAs($admin)->getJson('/api/orders')->assertJsonCount(3, 'data');
        $this->getJson('/api/reservations')->assertOk();
        $this->getJson('/api/dashboard')->assertJsonPath('data.orders', 3);

        $staff = User::factory()->create();
        $staff->assignRole('Staff');
        $this->actingAs($staff)->getJson('/api/orders')->assertJsonCount(0, 'data');
        $this->getJson('/api/reservations')->assertForbidden();
        $this->putJson('/api/orders/'.$id, $payload)->assertForbidden();
    }
}
