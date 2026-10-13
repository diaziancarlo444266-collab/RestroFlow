<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $permissions = collect(['records.view', 'records.create', 'records.update', 'records.delete'])
            ->map(fn ($name) => Permission::findOrCreate($name, 'web'));
        Role::findOrCreate('Admin', 'web')->syncPermissions($permissions);
        Role::findOrCreate('Staff', 'web')->syncPermissions($permissions->where('name', '!=', 'records.delete'));
        Role::findOrCreate('Viewer', 'web')->syncPermissions(['records.view']);
        Role::findOrCreate('Customer', 'web')->syncPermissions(['records.view']);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
