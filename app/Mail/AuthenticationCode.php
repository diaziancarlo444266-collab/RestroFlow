<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class AuthenticationCode extends Mailable
{
    public function __construct(public string $code) {}

    public function build(): static
    {
        return $this->subject('Your '.config('app.name').' verification code')
            ->view('mail.authentication-code');
    }
}
