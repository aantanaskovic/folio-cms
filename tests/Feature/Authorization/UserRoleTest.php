<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('allows a user to be assigned a role', function () {
    $user = User::factory()->create();
    $role = Role::create([
        'name' => 'contributor',
        'guard_name' => 'web',
    ]);

    $user->assignRole($role);

    expect($user->hasRole('contributor'))->toBeTrue()
        ->and($user->hasRole('admin'))->toBeFalse();
});

it('allows a role to grant permissions to its users', function () {
    $user = User::factory()->create();

    $createPermission = Permission::create([
        'name' => 'pages.create',
        'guard_name' => 'web',
    ]);

    Permission::create([
        'name' => 'pages.publish',
        'guard_name' => 'web',
    ]);

    $role = Role::create([
        'name' => 'contributor',
        'guard_name' => 'web',
    ]);

    $role->givePermissionTo($createPermission);
    $user->assignRole($role);

    expect($user->hasPermissionTo('pages.create'))->toBeTrue()
        ->and($user->hasPermissionTo('pages.publish'))->toBeFalse();
});

it('does not grant permissions to a user who has no corresponding role', function () {
    $permission = Permission::create([
        'name' => 'pages.publish',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();

    expect($user->hasPermissionTo($permission))->toBeFalse();
});

it('allows a user to have multiple roles', function () {
    $user = User::factory()->create();

    $contributor = Role::create([
        'name' => 'contributor',
        'guard_name' => 'web',
    ]);

    $reviewer = Role::create([
        'name' => 'reviewer',
        'guard_name' => 'web',
    ]);

    $user->assignRole($contributor, $reviewer);

    expect($user->hasRole('contributor'))->toBeTrue()
        ->and($user->hasRole('reviewer'))->toBeTrue()
        ->and($user->roles)->toHaveCount(2);
});

it('revokes role-based permissions when a role is removed', function () {
    $permission = Permission::create([
        'name' => 'pages.create',
        'guard_name' => 'web',
    ]);

    $role = Role::create([
        'name' => 'contributor',
        'guard_name' => 'web',
    ]);

    $role->givePermissionTo($permission);

    $user = User::factory()->create();
    $user->assignRole($role);

    expect($user->hasPermissionTo('pages.create'))->toBeTrue();

    $user->removeRole($role);

    expect($user->fresh()->hasPermissionTo('pages.create'))->toBeFalse();
});

it('allows a user to receive a permission directly without a role', function () {
    $permission = Permission::create([
        'name' => 'pages.publish',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create();

    $user->givePermissionTo($permission);

    expect($user->fresh()->hasPermissionTo('pages.publish'))->toBeTrue()
        ->and($user->fresh()->roles)->toBeEmpty();
});
