<?php

namespace Tests\Feature;

use App\Mail\AuthenticationCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_verifies_once_and_later_logins_do_not_send_another_code(): void
    {
        $this->seed();
        Mail::fake();
        $credentials = ['email' => 'customer@example.test', 'password' => 'StrongPassword123!'];
        $this->post('/register', [...$credentials, 'name' => 'Customer', 'password_confirmation' => $credentials['password']])
            ->assertRedirect('/email-otp');
        $this->assertGuest();
        $this->getJson('/api/orders')->assertUnauthorized();
        $this->post('/email-otp', ['code' => Mail::sent(AuthenticationCode::class)->last()->code])->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertNotNull(auth()->user()->email_verified_at);
        $this->post('/logout');
        Mail::fake();
        $this->post('/login', [...$credentials, 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->post('/login', [...$credentials, 'remember' => true])->assertRedirect('/');
        $this->assertAuthenticated();
        Mail::assertNothingSent();
        $this->getJson('/api/orders')->assertOk();
    }

    public function test_admin_username_and_password_sign_in_without_email_otp(): void
    {
        $this->seed();
        Mail::fake();
        $user = User::factory()->create(['username' => 'admin', 'password' => 'password']);
        $user->assignRole('Admin');
        $this->post('/login', ['email' => 'admin', 'password' => 'incorrect'])->assertSessionHasErrors('email');
        Mail::assertNothingSent();
        $this->assertGuest();
        $this->post('/login', ['email' => 'admin', 'password' => 'password'])->assertRedirect('/');
        Mail::assertNothingSent();
        $this->assertAuthenticatedAs($user);
        $this->getJson('/api/reservations')->assertOk();
    }

    private function beginLogin(): string
    {
        Mail::fake();
        $user = User::factory()->unverified()->create(['password' => 'StrongPassword123!']);
        $this->post('/login', ['email' => $user->email, 'password' => 'StrongPassword123!'])
            ->assertRedirect('/email-otp');
        $this->assertGuest();
        Mail::assertSent(AuthenticationCode::class, fn ($mail) => $mail->hasTo($user->email));

        return Mail::sent(AuthenticationCode::class)->last()->code;
    }

    public function test_wrong_codes_are_limited_even_when_correct_code_is_later_provided(): void
    {
        $code = $this->beginLogin();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/email-otp', ['code' => '000000'])->assertSessionHasErrors('code');
        }
        $this->post('/email-otp', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_expired_code_is_rejected(): void
    {
        $code = $this->beginLogin();
        $this->travel(11)->minutes();
        $this->post('/email-otp', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_resend_is_limited_and_replaces_the_code(): void
    {
        $this->beginLogin();
        $oldHash = Cache::get('email-otp:'.session('otp.challenge'))['hash'];
        $this->post('/email-otp/resend')->assertSessionHasErrors('code');
        Mail::assertSentCount(1);
        $this->travel(61)->seconds();
        $this->post('/email-otp/resend')->assertRedirect('/email-otp');
        Mail::assertSentCount(2);
        $this->assertNotSame($oldHash, Cache::get('email-otp:'.session('otp.challenge'))['hash']);
        $code = Mail::sent(AuthenticationCode::class)->last()->code;
        $this->post('/email-otp', ['code' => $code])->assertRedirect('/');
        $this->assertAuthenticated();
        $this->assertNotNull(auth()->user()->email_verified_at);
        $this->post('/logout');
        $this->post('/email-otp', ['code' => $code])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_delivery_failure_keeps_user_unauthenticated_and_allows_retry(): void
    {
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Mail unavailable'));
        $user = User::factory()->unverified()->create(['password' => 'StrongPassword123!']);
        $this->post('/login', ['email' => $user->email, 'password' => 'StrongPassword123!'])
            ->assertRedirect('/email-otp')->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->get('/email-otp')->assertOk();
    }
}
