<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Services\EmailOtp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VerifyLogin
{
    public static function user(Request $request): ?User
    {
        $identifier = Str::lower(trim((string) $request->input('email')));
        $user = User::where(str_contains($identifier, '@') ? 'email' : 'username', $identifier)->first();

        return $user && Hash::check((string) $request->input('password'), $user->password) ? $user : null;
    }

    public function handle(Request $request, $next)
    {
        $user = self::user($request);
        if ($user && ! $user->hasRole('Admin') && ! $user->email_verified_at) {
            return app(EmailOtp::class)->start($request, $user, $request->boolean('remember'));
        }

        return $next($request);
    }
}
