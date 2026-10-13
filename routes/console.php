<?php

use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('app:set-role {email} {role}', function () {
    $role = $this->argument('role');
    if (! in_array($role, ['Admin', 'Staff', 'Viewer', 'Customer'], true)) {
        $this->error('Role must be Admin, Staff, Viewer, or Customer.');

        return 1;
    }
    $user = User::where('email', $this->argument('email'))->first();
    if (! $user) {
        $this->error('Register this account first.');

        return 1;
    }
    $user->syncRoles([$role]);
    $this->info("Assigned {$role} to {$user->email}.");

    return 0;
})->purpose('Assign a restaurant role to an existing account (server administrator only)');
