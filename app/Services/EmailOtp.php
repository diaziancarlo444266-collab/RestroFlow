<?php

namespace App\Services;

use App\Mail\AuthenticationCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class EmailOtp
{
    public function start(Request $request, User $user, bool $remember)
    {
        Cache::forget('email-otp:'.$request->session()->get('otp.challenge', ''));
        $request->session()->regenerate();
        $request->session()->put('otp', ['challenge' => Str::random(64), 'user_id' => $user->id, 'remember' => $remember]);

        return $this->send($request, $user);
    }

    public function send(Request $request, User $user)
    {
        $key = 'otp-send:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 1)) {
            return redirect()->route('otp.show')->withErrors(['code' => 'Please wait '.RateLimiter::availableIn($key).' seconds before requesting another code.']);
        }
        RateLimiter::hit($key, 60);
        $code = (string) random_int(100000, 999999);
        $challenge = 'email-otp:'.$request->session()->get('otp.challenge');
        Cache::forget($challenge);
        try {
            Mail::to($user->email)->send(new AuthenticationCode($code));
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('otp.show')->withErrors(['code' => 'Unable to send your code. Please try resending in one minute.']);
        }
        Cache::put($challenge, ['hash' => Hash::make($code), 'user_id' => $user->id, 'email' => $user->email], now()->addMinutes(10));

        return redirect()->route('otp.show')->with('status', 'A verification code has been sent to your email.');
    }
}
