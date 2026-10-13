<?php

namespace Tests\Feature;

use App\Mail\AuthenticationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Mail::fake();
    }

    public function test_guests_cannot_read_or_write_restaurant_data(): void
    {
        $this->get('/')->assertRedirect('/login');
        foreach (['orders', 'reservations', 'inventory'] as $module) {
            $this->getJson('/api/'.$module)->assertUnauthorized();
            $this->postJson('/api/'.$module, [])->assertUnauthorized();
            $this->putJson('/api/'.$module.'/1', [])->assertUnauthorized();
            $this->deleteJson('/api/'.$module.'/1')->assertUnauthorized();
        }
        $this->getJson('/api/dashboard')->assertUnauthorized();
        $this->getJson('/api/lookups')->assertUnauthorized();
    }

    public function test_fortify_registration_hashes_password_and_ignores_requested_admin_role(): void
    {
        $this->get('/register')->assertInertia(fn (Assert $page) => $page->component('Auth/Register'));
        $this->post('/register', [
            'name' => 'New Viewer', 'email' => 'viewer@example.test',
            'password' => 'StrongPassword123!', 'password_confirmation' => 'StrongPassword123!',
            'role' => 'Admin', 'roles' => ['Admin'], 'permissions' => ['records.delete'],
        ])->assertRedirect('/email-otp');
        $user = User::where('email', 'viewer@example.test')->firstOrFail();
        $this->assertGuest();
        $this->getJson('/api/dashboard')->assertUnauthorized();
        $code = Mail::sent(AuthenticationCode::class)->last()->code;
        $this->post('/email-otp', ['code' => $code])->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('StrongPassword123!', $user->password));
        $this->assertTrue($user->hasExactRoles(['Customer']));
        $this->assertFalse($user->can('records.delete'));
    }

    public function test_registration_rejects_invalid_input(): void
    {
        $this->post('/register', ['name' => '', 'email' => 'invalid', 'password' => 'short', 'password_confirmation' => 'different'])
            ->assertSessionHasErrors(['name', 'email', 'password']);
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
    }

    public function test_fortify_login_logout_and_inertia_auth_props(): void
    {
        $user = User::factory()->create(['password' => 'StrongPassword123!']);
        $user->assignRole('Staff');
        $this->get('/login')->assertInertia(fn (Assert $page) => $page->component('Auth/Login'));
        $this->post('/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'StrongPassword123!'])->assertRedirect('/');
        Mail::assertNothingSent();
        $this->assertAuthenticatedAs($user);
        $this->get('/')->assertInertia(fn (Assert $page) => $page->component('Restaurant')
            ->where('auth.user.email', $user->email)->where('auth.roles.0', 'Staff')->missing('auth.user.password'));
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_repeated_bad_logins_are_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/login', ['email' => 'missing@example.test', 'password' => 'wrong'])->assertUnprocessable();
        }
        $this->postJson('/login', ['email' => 'missing@example.test', 'password' => 'wrong'])->assertStatus(429);
    }

    public function test_viewer_cannot_bypass_permissions_with_direct_api_requests(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Viewer');
        $this->actingAs($user);
        $this->get('/')->assertOk();
        foreach (['orders', 'reservations', 'inventory'] as $module) {
            $this->getJson('/api/'.$module)->assertStatus($module !== 'inventory' ? 200 : 403);
            $this->postJson('/api/'.$module, [])->assertStatus($module !== 'inventory' ? 422 : 403);
            $this->putJson('/api/'.$module.'/1', [])->assertForbidden();
            $this->deleteJson('/api/'.$module.'/1')->assertForbidden();
        }
    }

    public function test_staff_can_create_and_update_but_cannot_delete(): void
    {
        $user = User::factory()->create();
        $user->assignRole('Staff');
        $this->actingAs($user);
        $payload = ['item_name' => 'Role Test Rice', 'quantity' => 5, 'unit' => 'kg', 'reorder_level' => 2];
        $id = $this->postJson('/api/inventory', $payload)->assertCreated()->json('data.id');
        $this->putJson('/api/inventory/'.$id, [...$payload, 'quantity' => 10])->assertOk();
        $this->deleteJson('/api/inventory/'.$id)->assertForbidden();
        $this->assertDatabaseHas('inventory_items', ['id' => $id, 'quantity' => 10]);
    }

    public function test_user_without_a_role_is_denied_and_role_assignment_command_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->getJson('/api/dashboard')->assertForbidden();
        $this->artisan('app:set-role', ['email' => $user->email, 'role' => 'Invalid'])->assertExitCode(1);
        $this->artisan('app:set-role', ['email' => $user->email, 'role' => 'Admin'])->assertSuccessful();
        $this->assertTrue($user->fresh()->hasRole('Admin'));
    }
}
