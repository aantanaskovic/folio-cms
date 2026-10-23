<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'users.administration',
            'pages.administration',
            'pages.view',
            'pages.create',
            'pages.update',
            'pages.delete',
            'pages.submit-for-review',
            'pages.review',
            'pages.publish',
            'users.view',
            'users.manage',
            'roles.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

        if ($admin->wasRecentlyCreated) {
            $admin->syncPermissions($permissions);
        }

        $contributor = Role::firstOrCreate([
            'name' => 'contributor',
            'guard_name' => 'web',
        ]);

        if ($contributor->wasRecentlyCreated) {
            $contributor->syncPermissions([
                'pages.administration',
                'pages.view',
                'pages.create',
                'pages.update',
                'pages.submit-for-review',
            ]);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
