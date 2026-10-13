<?php

require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (! app()->environment('testing') || config('database.default') !== 'sqlite' || config('cache.default') !== 'database'
    || basename(config('database.connections.sqlite.database')) !== 'browser-auth.sqlite') {
    throw new RuntimeException('Browser fixtures require the isolated browser test database.');
}

\Illuminate\Support\Facades\Cache::flush();

foreach (['Admin', 'Staff', 'Viewer', 'Admin2'] as $role) {
    $user = \App\Models\User::updateOrCreate(['email' => strtolower($role).'@browser.test'], [
        'name' => $role.' Browser Test', 'password' => 'BrowserTestPassword123!', 'email_verified_at' => now(),
    ]);
    $user->syncRoles([$role === 'Admin2' ? 'Admin' : $role]);
}
