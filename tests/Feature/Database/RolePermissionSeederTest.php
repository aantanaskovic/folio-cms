<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

it('creates the required roles and permissions', function () {
    $this->seed(RolePermissionSeeder::class);

    expect(Role::where('name', 'admin')->where('guard_name', 'web')->exists())
        ->toBeTrue()
        ->and(Role::where('name', 'contributor')->where('guard_name', 'web')->exists())
        ->toBeTrue();

    foreach (
        [
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
        ] as $permission
    ) {
        expect(
            Permission::where('name', $permission)
                ->where('guard_name', 'web')
                ->exists(),
        )->toBeTrue();
    }
});

it('assigns the default permissions to the admin role', function () {
    $this->seed(RolePermissionSeeder::class);

    $admin = Role::findByName('admin', 'web');

    expect($admin->permissions->pluck('name')->sort()->values()->all())
        ->toBe([
            'pages.administration',
            'pages.create',
            'pages.delete',
            'pages.publish',
            'pages.review',
            'pages.submit-for-review',
            'pages.update',
            'pages.view',
            'roles.manage',
            'users.administration',
            'users.manage',
            'users.view',
        ]);
});

it('assigns only content workflow permissions to contributors', function () {
    $this->seed(RolePermissionSeeder::class);

    $contributor = Role::findByName('contributor', 'web');

    expect($contributor->permissions->pluck('name')->sort()->values()->all())
        ->toBe([
            'pages.administration',
            'pages.create',
            'pages.submit-for-review',
            'pages.update',
            'pages.view',
        ]);
});

it('can be executed more than once without duplicating roles or permissions', function () {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(RolePermissionSeeder::class);

    expect(Role::where('guard_name', 'web')->count())
        ->toBe(2)
        ->and(Permission::where('guard_name', 'web')->count())
        ->toBe(12);
});

it('allows a newly seeded contributor to receive the role', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();

    $user->assignRole('contributor');

    expect($user->hasRole('contributor'))
        ->toBeTrue()
        ->and($user->can('pages.create'))
        ->toBeTrue()
        ->and($user->can('pages.publish'))
        ->toBeFalse();
});

it('preserves manually changed permissions when the seeder runs again', function () {
    $this->seed(RolePermissionSeeder::class);

    $contributor = Role::findByName('contributor', 'web');

    $contributor->syncPermissions(['pages.view']);

    $this->seed(RolePermissionSeeder::class);

    expect($contributor->fresh()->permissions->pluck('name')->all())
        ->toBe(['pages.view']);
});

it('creates permissions that do not exist yet', function () {
    $this->seed(RolePermissionSeeder::class);

    Permission::query()
        ->where('name', 'pages.publish')
        ->where('guard_name', 'web')
        ->delete();

    $this->seed(RolePermissionSeeder::class);

    expect(
        Permission::query()
            ->where('name', 'pages.publish')
            ->where('guard_name', 'web')
            ->exists(),
    )->toBeTrue();
});
