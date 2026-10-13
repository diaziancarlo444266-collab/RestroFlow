<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_pages_redirect_signed_in_users_and_pages_are_not_cached(): void
    {
        $this->seed();
        $user = User::factory()->create();
        $user->assignRole('Customer');
        $this->get('/login')->assertOk()->assertHeader('Pragma', 'no-cache');
        $this->actingAs($user);
        foreach (['/login', '/register', '/email-otp'] as $path) {
            $this->get($path)->assertRedirect('/');
        }
        $response = $this->get('/')->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->post('/logout')->assertRedirect('/');
        $this->get('/')->assertRedirect('/login');
        $this->getJson('/api/orders')->assertUnauthorized();
    }
}
