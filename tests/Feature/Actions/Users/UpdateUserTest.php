<?php

use App\Actions\Users\UpdateUser;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('does not allow demoting the last administrator', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');
    $user->givePermissionTo('users.manage');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(User::role('admin')->count())->toBe(1);

    expect(fn() => app(UpdateUser::class)->handle($user, $admin, [
        'name' => $admin->name,
        'email' => $admin->email,
        'role' => 'contributor',
        'password' => '',
    ]))->toThrow(ValidationException::class);

    expect($admin->fresh()->hasRole('admin'))->toBeTrue()
        ->and(User::role('admin')->count())->toBe(1);
});

it('does not allow an admin to demote their own account', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(fn() => app(UpdateUser::class)->handle($admin, $admin, [
        'name' => $admin->name,
        'email' => $admin->email,
        'role' => 'contributor',
        'password' => '',
    ]))->toThrow(ValidationException::class);

    expect($admin->fresh()->hasRole('admin'))->toBeTrue();
});

it('rejects an invalid role when updating a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    expect(fn() => app(UpdateUser::class)->handle($admin, $contributor, [
        'name' => $contributor->name,
        'email' => $contributor->email,
        'role' => 'editor',
        'password' => '',
    ]))->toThrow(ValidationException::class);

    expect($contributor->fresh()->hasRole('contributor'))->toBeTrue()
        ->and($contributor->fresh()->hasRole('admin'))->toBeFalse();
});

it('rejects a missing role when updating a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    expect(fn() => app(UpdateUser::class)->handle($admin, $contributor, [
        'name' => $contributor->name,
        'email' => $contributor->email,
        'password' => '',
    ]))->toThrow(ValidationException::class);

    expect($contributor->fresh()->hasRole('contributor'))->toBeTrue();
});

it('rejects an invalid email address when updating a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    expect(fn() => app(UpdateUser::class)->handle($admin, $contributor, [
        'name' => $contributor->name,
        'email' => 'not-an-email',
        'role' => 'contributor',
        'password' => '',
    ]))->toThrow(ValidationException::class);

    expect($contributor->fresh()->email)->not->toBe('not-an-email');
});

it('rejects an email already assigned to another user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $anotherUser = User::factory()->create();

    expect(fn() => app(UpdateUser::class)->handle($admin, $contributor, [
        'name' => $contributor->name,
        'email' => $anotherUser->email,
        'role' => 'contributor',
        'password' => '',
    ]))->toThrow(ValidationException::class);

    expect($contributor->fresh()->email)->not->toBe($anotherUser->email);
});

it('rejects a missing name when updating a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    expect(fn() => app(UpdateUser::class)->handle($admin, $contributor, [
        'name' => '',
        'email' => $contributor->email,
        'role' => 'contributor',
        'password' => '',
    ]))->toThrow(ValidationException::class);

    expect($contributor->fresh()->name)->not->toBe('');
});

it('rejects a password shorter than eight characters when updating a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $originalPassword = $contributor->password;

    expect(fn() => app(UpdateUser::class)->handle($admin, $contributor, [
        'name' => $contributor->name,
        'email' => $contributor->email,
        'role' => 'contributor',
        'password' => 'short',
    ]))->toThrow(ValidationException::class);

    expect($contributor->fresh()->password)->toBe($originalPassword);
});

it('preserves the existing password when the password is empty', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $originalPassword = $contributor->password;

    $updatedUser = app(UpdateUser::class)->handle($admin, $contributor, [
        'name' => $contributor->name,
        'email' => $contributor->email,
        'role' => 'contributor',
        'password' => '',
    ]);

    expect($updatedUser->fresh()->password)->toBe($originalPassword);
});

it('rejects a name longer than 255 characters when updating a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $originalName = $contributor->name;

    expect(fn() => app(UpdateUser::class)->handle($admin, $contributor, [
        'name' => str_repeat('a', 256),
        'email' => $contributor->email,
        'role' => 'contributor',
        'password' => '',
    ]))->toThrow(ValidationException::class);

    expect($contributor->fresh()->name)->toBe($originalName);
});

it('rejects a password longer than 255 characters when updating a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $originalPassword = $contributor->password;

    expect(fn() => app(UpdateUser::class)->handle($admin, $contributor, [
        'name' => $contributor->name,
        'email' => $contributor->email,
        'role' => 'contributor',
        'password' => str_repeat('a', 256),
    ]))->toThrow(ValidationException::class);

    expect($contributor->fresh()->password)->toBe($originalPassword);
});

it('does not allow a user without manage permission to update another user', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $target = User::factory()->create([
        'name' => 'Original Name',
    ]);
    $target->assignRole('contributor');

    expect(fn() => app(UpdateUser::class)->handle($contributor, $target, [
        'name' => 'Changed Name',
        'email' => $target->email,
        'role' => 'contributor',
        'password' => '',
    ]))->toThrow(AuthorizationException::class);

    expect($target->fresh()->name)->toBe('Original Name');
});
