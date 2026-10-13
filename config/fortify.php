<?php

use Laravel\Fortify\Features;

return [
    'guard' => 'web',
    'passwords' => 'users',
    'username' => 'email',
    'email' => 'email',
    'lowercase_usernames' => true,
    'home' => '/',
    'prefix' => '',
    'domain' => null,
    'middleware' => ['web', 'throttle:auth-forms'],
    'auth_middleware' => 'auth',
    'views' => true,
    'limiters' => ['login' => 'login'],
    'features' => [Features::registration(), Features::resetPasswords()],
];
