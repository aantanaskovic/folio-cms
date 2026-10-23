<?php

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Gate;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows an admin to manage users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(Gate::forUser($admin)->allows('viewAny', User::class))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('create', User::class))->toBeTrue();
});

it('denies a contributor access to user administration', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    expect(Gate::forUser($contributor)->allows('viewAny', User::class))->toBeFalse()
        ->and(Gate::forUser($contributor)->allows('create', User::class))->toBeFalse();
});

it('denies user administration to users without a role', function () {
    $user = User::factory()->create();

    expect(Gate::forUser($user)->allows('viewAny', User::class))->toBeFalse()
        ->and(Gate::forUser($user)->allows('create', User::class))->toBeFalse();
});

it('allows an admin to view and update users', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $target = User::factory()->create();

    expect(Gate::forUser($admin)->allows('view', $target))->toBeTrue()
        ->and(Gate::forUser($admin)->allows('update', $target))->toBeTrue();
});

it('denies a contributor access to individual user records', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $target = User::factory()->create();

    expect(Gate::forUser($contributor)->allows('view', $target))->toBeFalse()
        ->and(Gate::forUser($contributor)->allows('update', $target))->toBeFalse();
});

it('denies individual user access to users without a role', function () {
    $user = User::factory()->create();
    $target = User::factory()->create();

    expect(Gate::forUser($user)->allows('view', $target))->toBeFalse()
        ->and(Gate::forUser($user)->allows('update', $target))->toBeFalse();
});

it('does not allow an admin to delete themselves', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(Gate::forUser($admin)->allows('delete', $admin))->toBeFalse();
});

it('does not allow deleting the last remaining admin', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $targetAdmin = User::factory()->create();
    $targetAdmin->assignRole('admin');

    // Remove the acting admin's role so the target becomes the only admin.
    $admin->removeRole('admin');

    expect(Gate::forUser($admin)->allows('delete', $targetAdmin))->toBeFalse();
});

it('allows an admin to delete another admin when multiple admins exist', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $anotherAdmin = User::factory()->create();
    $anotherAdmin->assignRole('admin');

    expect(Gate::forUser($admin)->allows('delete', $anotherAdmin))->toBeTrue();
});

it('allows an admin to delete a contributor', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    expect(Gate::forUser($admin)->allows('delete', $contributor))->toBeTrue();
});

it('denies contributors and users without a role from deleting users', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $userWithoutRole = User::factory()->create();
    $target = User::factory()->create();

    expect(Gate::forUser($contributor)->allows('delete', $target))->toBeFalse()
        ->and(Gate::forUser($userWithoutRole)->allows('delete', $target))->toBeFalse();
});
