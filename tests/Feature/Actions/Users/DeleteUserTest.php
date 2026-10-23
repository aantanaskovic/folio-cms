<?php

use App\Actions\Users\DeleteUser;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows an admin to delete a contributor', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    app(DeleteUser::class)->handle($admin, $contributor);

    $this->assertDatabaseMissing('users', [
        'id' => $contributor->id,
    ]);
});

it('does not allow a contributor to delete another user', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $anotherUser = User::factory()->create();
    $anotherUser->assignRole('contributor');

    expect(
        fn() => app(DeleteUser::class)->handle($contributor, $anotherUser)
    )->toThrow(AuthorizationException::class);

    $this->assertDatabaseHas('users', [
        'id' => $anotherUser->id,
    ]);
});

it('does not allow a user to delete their own account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(
        fn() => app(DeleteUser::class)->handle($admin, $admin)
    )->toThrow(AuthorizationException::class);

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});

it('does not allow deleting the last administrator', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    expect(
        fn() => app(DeleteUser::class)->handle($contributor, $admin)
    )->toThrow(AuthorizationException::class);

    $this->assertDatabaseHas('users', [
        'id' => $admin->id,
    ]);
});

it('allows deleting an administrator when another administrator exists', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $anotherAdmin = User::factory()->create();
    $anotherAdmin->assignRole('admin');

    app(DeleteUser::class)->handle($admin, $anotherAdmin);

    $this->assertDatabaseMissing('users', [
        'id' => $anotherAdmin->id,
    ]);
});

it('does not allow a user without the manage permission to delete another user', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $anotherUser = User::factory()->create();
    $anotherUser->assignRole('contributor');

    expect($user->can('users.manage'))->toBeFalse();

    expect(
        fn() => app(DeleteUser::class)->handle($user, $anotherUser)
    )->toThrow(AuthorizationException::class);

    $this->assertDatabaseHas('users', [
        'id' => $anotherUser->id,
    ]);
});
