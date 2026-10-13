<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CustomerReservationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_reservations_are_owned_pending_and_private(): void
    {
        $this->seed();
        $customer = User::factory()->create(['name' => 'Same Name']);
        $customer->assignRole('Customer');
        $other = User::factory()->create(['name' => 'Same Name']);
        $other->assignRole('Viewer');
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $payload = ['phone' => '09123456789', 'table_id' => DB::table('restaurant_tables')->value('id'),
            'reservation_date' => now()->addDay()->format('Y-m-d'), 'reservation_time' => '18:30', 'guests' => 2];
        $this->actingAs($other);
        $otherId = $this->postJson('/api/reservations', $payload)->assertCreated()->json('data.id');
        $this->actingAs($customer);
        $id = $this->postJson('/api/reservations', [...$payload, 'user_id' => $other->id, 'status' => 'Confirmed', 'customer_name' => 'Spoofed'])
            ->assertCreated()->assertJsonPath('data.user_id', $customer->id)->assertJsonPath('data.status', 'Pending')
            ->assertJsonPath('data.customer_name', 'Same Name')->json('data.id');
        $this->getJson('/api/reservations?search=Same')->assertJsonCount(1, 'data')->assertJsonPath('data.0.id', $id);
        $this->getJson('/api/dashboard')->assertJsonPath('data.reservations', 1);
        foreach ([$id, $otherId] as $recordId) {
            $this->putJson('/api/reservations/'.$recordId, $payload)->assertForbidden();
            $this->deleteJson('/api/reservations/'.$recordId)->assertForbidden();
        }
        $this->postJson('/api/reservations', [...$payload, 'table_id' => 99999, 'guests' => 0])->assertUnprocessable();
        DB::table('reservations')->insert([...$payload, 'customer_name' => 'Same Name', 'status' => 'Pending']);
        $this->getJson('/api/reservations')->assertJsonCount(1, 'data');
        $this->actingAs($admin)->getJson('/api/reservations')->assertJsonCount(3, 'data');
        $this->putJson('/api/reservations/'.$id, [...$payload, 'customer_name' => 'Same Name', 'status' => 'Confirmed'])->assertOk();
        $this->actingAs($customer)->getJson('/api/reservations')->assertJsonPath('data.0.status', 'Confirmed');
    }
}
