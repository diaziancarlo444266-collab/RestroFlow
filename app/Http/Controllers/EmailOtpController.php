<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\EmailOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class EmailOtpController extends Controller
{
    public function show(Request $request)
    {
        if (! $request->session()->has('otp.user_id')) {
            return redirect()->route('login');
        }

        return Inertia::render('Auth/VerifyOtp', [
            'verifyUrl' => route('otp.verify'), 'resendUrl' => route('otp.resend'),
            'status' => $request->session()->get('status'),
        ]);
    }

    public function resend(Request $request)
    {
        $user = User::find($request->session()->get('otp.user_id'));
        if (! $user) {
            return redirect()->route('login');
        }

        return app(EmailOtp::class)->send($request, $user);
    }

    public function verify(Request $request)
    {
        $request->validate(['code' => ['required', 'regex:/^[0-9]{6}$/']]);
        $key = 'email-otp:'.$request->session()->get('otp.challenge', '');

        return Cache::lock($key.':lock', 10)->block(5, function () use ($request, $key) {
            $challenge = Cache::get($key);
            $user = User::find($request->session()->get('otp.user_id'));
            $attemptKey = 'otp-verify:'.($user?->id ?? $request->ip());
            if (RateLimiter::tooManyAttempts($attemptKey, 5)) {
                throw ValidationException::withMessages(['code' => 'Too many attempts. Please wait 10 minutes before trying again.']);
            }
            if (! $challenge || ! $user || $challenge['user_id'] !== $user->id || $challenge['email'] !== $user->email) {
                throw ValidationException::withMessages(['code' => 'Your code has expired. Please request a new code.']);
            }
            RateLimiter::hit($attemptKey, 600);
            if (! Hash::check($request->input('code'), $challenge['hash'])) {
                throw ValidationException::withMessages(['code' => 'The verification code is incorrect.']);
            }
            Cache::forget($key);
            RateLimiter::clear($attemptKey);
            if (! $user->email_verified_at) {
                $user->forceFill(['email_verified_at' => now()])->save();
            }
            Auth::login($user, $request->session()->get('otp.remember', false));
            $request->session()->forget('otp');
            $request->session()->regenerate();

            return redirect()->intended('/');
        });
    }

    public function logout(Request $request)
    {
        Cache::forget('email-otp:'.$request->session()->get('otp.challenge', ''));
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
