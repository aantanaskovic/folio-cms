<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('creates the required roles and permissions', function () {
    app(RolePermissionSeeder::class)->run();

    expect(Role::where('name', 'admin')->where('guard_name', 'web')->exists())
        ->toBeTrue()
        ->and(Role::where('name', 'contributor')->where('guard_name', 'web')->exists())
        ->toBeTrue();

    foreach (
        [
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
        ] as $permission
    ) {
        expect(
            Permission::where('name', $permission)
                ->where('guard_name', 'web')
                ->exists()
        )->toBeTrue();
    }
});

it('assigns all permissions to the admin role', function () {
    app(RolePermissionSeeder::class)->run();

    $admin = Role::findByName('admin', 'web');

    expect($admin->permissions)->toHaveCount(10);

    foreach ($admin->permissions as $permission) {
        expect($admin->hasPermissionTo($permission))->toBeTrue();
    }
});

it('assigns only content workflow permissions to contributors', function () {
    app(RolePermissionSeeder::class)->run();

    $contributor = Role::findByName('contributor', 'web');

    expect($contributor->hasPermissionTo('pages.view'))->toBeTrue()
        ->and($contributor->hasPermissionTo('pages.create'))->toBeTrue()
        ->and($contributor->hasPermissionTo('pages.update'))->toBeTrue()
        ->and($contributor->hasPermissionTo('pages.submit-for-review'))->toBeTrue()
        ->and($contributor->hasPermissionTo('pages.publish'))->toBeFalse()
        ->and($contributor->hasPermissionTo('users.manage'))->toBeFalse()
        ->and($contributor->hasPermissionTo('roles.manage'))->toBeFalse();
});

it('can be executed more than once without duplicating roles or permissions', function () {
    app(RolePermissionSeeder::class)->run();
    app(RolePermissionSeeder::class)->run();

    expect(Role::where('guard_name', 'web')->count())->toBe(2)
        ->and(Permission::where('guard_name', 'web')->count())->toBe(10);
});

it('allows a newly seeded contributor to receive the role', function () {
    app(RolePermissionSeeder::class)->run();

    $user = User::factory()->create();

    $user->assignRole('contributor');

    expect($user->hasRole('contributor'))->toBeTrue()
        ->and($user->can('pages.create'))->toBeTrue()
        ->and($user->can('pages.publish'))->toBeFalse();
});
