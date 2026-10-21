<?php

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create([
        'name' => 'contributor',
        'guard_name' => 'web',
    ]);

    Role::create([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);
});

it('creates a user with valid registration data', function () {
    $user = app(CreateNewUser::class)->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Jane Doe')
        ->and($user->email)->toBe('jane@example.com')
        ->and($user->exists)->toBeTrue();
});

it('hashes the registered user password', function () {
    $user = app(CreateNewUser::class)->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
    ]);

    expect($user->password)
        ->not->toBe('StrongPassword123!')
        ->and(Hash::check('StrongPassword123!', $user->password))->toBeTrue();
});

it('assigns the contributor role to a new user', function () {
    $user = app(CreateNewUser::class)->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
    ]);

    expect($user->hasRole('contributor'))->toBeTrue()
        ->and($user->hasRole('admin'))->toBeFalse();
});

it('does not allow registration to assign an admin role through input', function () {
    $user = app(CreateNewUser::class)->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
        'role' => 'admin',
        'is_admin' => true,
    ]);

    expect($user->hasRole('admin'))->toBeFalse()
        ->and($user->hasRole('contributor'))->toBeTrue();
});

it('rejects an invalid email address', function () {
    app(CreateNewUser::class)->create([
        'name' => 'Jane Doe',
        'email' => 'not-an-email',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
    ]);
})->throws(ValidationException::class);

it('rejects a duplicate email address', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
    ]);

    app(CreateNewUser::class)->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
    ]);
})->throws(ValidationException::class);

it('rejects registration without a name', function () {
    app(CreateNewUser::class)->create([
        'email' => 'jane@example.com',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
    ]);
})->throws(ValidationException::class);

it('rejects registration with mismatched password confirmation', function () {
    app(CreateNewUser::class)->create([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'DifferentPassword123!',
    ]);
})->throws(ValidationException::class);
