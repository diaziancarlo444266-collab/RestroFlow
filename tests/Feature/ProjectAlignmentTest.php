<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ProjectAlignmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_fortify_reset_link_changes_password_and_cannot_be_reused(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->get('/forgot-password')->assertInertia(fn (Assert $page) => $page->component('Auth/ForgotPassword'));
        $this->post('/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;

            return true;
        });
        $this->get('/reset-password/'.$token.'?email='.urlencode($user->email))->assertInertia(fn (Assert $page) => $page->component('Auth/ResetPassword'));
        $payload = ['email' => $user->email, 'token' => $token, 'password' => 'NewPassword123!', 'password_confirmation' => 'NewPassword123!'];
        $this->post('/reset-password', [...$payload, 'token' => 'invalid'])->assertSessionHasErrors('email');
        $this->post('/reset-password', [...$payload, 'password_confirmation' => 'different'])->assertSessionHasErrors('password');
        $this->post('/reset-password', $payload)->assertRedirect('/login');
        $this->assertTrue(Hash::check('NewPassword123!', $user->fresh()->password));
        $this->post('/reset-password', $payload)->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_resource_pages_relationships_and_ownership(): void
    {
        $this->seed();
        $customer = User::factory()->create();
        $customer->assignRole('Customer');
        $other = User::factory()->create();
        $other->assignRole('Customer');
        $this->actingAs($customer);
        $id = $this->postJson('/api/orders', ['menu_item_id' => DB::table('menu_items')->value('id'), 'quantity' => 1])->assertCreated()->json('data.id');
        $order = $customer->orders()->firstOrFail();
        $this->assertTrue($order->user->is($customer));
        $this->assertTrue($order->menuItem->orders->contains($order));
        $this->assertNotNull($order->updated_at);
        $this->get('/orders')->assertInertia(fn (Assert $page) => $page->component('Restaurant')->where('initialPage', 'orders'));
        $this->get('/orders/create')->assertOk();
        $this->get('/orders/'.$id)->assertInertia(fn (Assert $page) => $page->component('RecordShow')->where('record.id', $id));
        $this->get('/orders/'.$id.'/edit')->assertForbidden();
        $this->get('/inventory')->assertForbidden();
        $this->actingAs($other)->get('/orders/'.$id)->assertNotFound();
        $this->getJson('/api/orders/'.$id)->assertNotFound();
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        $this->actingAs($admin)->get('/orders/'.$id.'/edit')->assertOk();
    }
}
