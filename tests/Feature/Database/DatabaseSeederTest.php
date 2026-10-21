<?php

use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('seeds the required roles and permissions through the main database seeder', function () {
    Artisan::call('db:seed');

    expect(Role::where('name', 'admin')->where('guard_name', 'web')->exists())
        ->toBeTrue()
        ->and(Role::where('name', 'contributor')->where('guard_name', 'web')->exists())
        ->toBeTrue()
        ->and(Permission::where('name', 'pages.publish')->where('guard_name', 'web')->exists())
        ->toBeTrue()
        ->and(Permission::where('name', 'users.manage')->where('guard_name', 'web')->exists())
        ->toBeTrue();
});

it('can run the main database seeder repeatedly', function () {
    Artisan::call('db:seed');
    Artisan::call('db:seed');

    expect(Role::where('guard_name', 'web')->count())->toBe(2)
        ->and(Permission::where('guard_name', 'web')->count())->toBe(10);
});
