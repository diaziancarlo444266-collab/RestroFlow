<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => fn () => [
                'user' => $request->user()?->only('id', 'name', 'email'),
                'isCustomer' => $request->user()?->isCustomer() ?? false,
                'roles' => $request->user()?->getRoleNames() ?? [],
                'permissions' => $request->user()?->getAllPermissions()->pluck('name') ?? [],
            ],
            'urls' => fn () => [
                'home' => url('/'), 'login' => route('login'),
                'forgotPassword' => route('password.request'), 'resetPassword' => route('password.update'), 'cancelOtp' => route('otp.cancel'),
                'register' => route('register'), 'logout' => route('logout'),
            ],
        ];
    }
}
