<?php

namespace App\Http\Responses;

use App\Services\EmailOtp;
use Illuminate\Support\Facades\Auth;

class RegisterResponse implements \Laravel\Fortify\Contracts\RegisterResponse
{
    public function toResponse($request)
    {
        $user = $request->user();
        Auth::logout();

        return app(EmailOtp::class)->start($request, $user, false);
    }
}
