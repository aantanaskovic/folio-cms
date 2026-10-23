<?php

use App\Actions\Users\CreateUser;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('creates a user and assigns the requested role', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = app(CreateUser::class)->handle($admin, [
        'name' => 'New Contributor',
        'email' => 'new-contributor@example.com',
        'password' => 'SecurePassword123!',
        'role' => 'contributor',
    ]);

    expect($user->name)->toBe('New Contributor')
        ->and($user->hasRole('contributor'))->toBeTrue()
        ->and($user->hasRole('admin'))->toBeFalse()
        ->and(Hash::check('SecurePassword123!', $user->password))->toBeTrue();
});

it('denies creation when the user lacks permission', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    expect(fn() => app(CreateUser::class)->handle($contributor, [
        'name' => 'Unauthorized User',
        'email' => 'unauthorized@example.com',
        'password' => 'SecurePassword123!',
        'role' => 'contributor',
    ]))->toThrow(AuthorizationException::class);

    $this->assertDatabaseMissing('users', [
        'email' => 'unauthorized@example.com',
    ]);
});

it('rejects unsupported roles without creating a user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(fn() => app(CreateUser::class)->handle($admin, [
        'name' => 'Unsupported Role',
        'email' => 'unsupported@example.com',
        'password' => 'SecurePassword123!',
        'role' => 'super-admin',
    ]))->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('users', [
        'email' => 'unsupported@example.com',
    ]);
});

it('rejects an invalid email address', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(fn() => app(CreateUser::class)->handle($admin, [
        'name' => 'New Contributor',
        'email' => 'invalid-email',
        'password' => 'SecurePassword123!',
        'role' => 'contributor',
    ]))->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('users', [
        'email' => 'invalid-email',
    ]);
});

it('rejects an email address already assigned to another user', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    expect(fn() => app(CreateUser::class)->handle($admin, [
        'name' => 'New Contributor',
        'email' => 'existing@example.com',
        'password' => 'SecurePassword123!',
        'role' => 'contributor',
    ]))->toThrow(ValidationException::class);

    expect(User::query()->where('email', 'existing@example.com')->count())
        ->toBe(1);
});

it('rejects a missing name', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(fn() => app(CreateUser::class)->handle($admin, [
        'email' => 'new-contributor@example.com',
        'password' => 'SecurePassword123!',
        'role' => 'contributor',
    ]))->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('users', [
        'email' => 'new-contributor@example.com',
    ]);
});

it('rejects a password shorter than eight characters', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(fn() => app(CreateUser::class)->handle($admin, [
        'name' => 'New Contributor',
        'email' => 'new-contributor@example.com',
        'password' => 'short',
        'role' => 'contributor',
    ]))->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('users', [
        'email' => 'new-contributor@example.com',
    ]);
});

it('rejects a missing password', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(fn() => app(CreateUser::class)->handle($admin, [
        'name' => 'New Contributor',
        'email' => 'new-contributor@example.com',
        'role' => 'contributor',
    ]))->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('users', [
        'email' => 'new-contributor@example.com',
    ]);
});

it('rejects a missing email address', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(fn() => app(CreateUser::class)->handle($admin, [
        'name' => 'New Contributor',
        'password' => 'SecurePassword123!',
        'role' => 'contributor',
    ]))->toThrow(ValidationException::class);
});

it('rejects a name longer than 255 characters', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect(fn() => app(CreateUser::class)->handle($admin, [
        'name' => str_repeat('a', 256),
        'email' => 'new-contributor@example.com',
        'password' => 'SecurePassword123!',
        'role' => 'contributor',
    ]))->toThrow(ValidationException::class);

    $this->assertDatabaseMissing('users', [
        'email' => 'new-contributor@example.com',
    ]);
});

it('sends an email verification notification when an admin creates a user', function () {
    Notification::fake();

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $user = app(CreateUser::class)->handle($admin, [
        'name' => 'New Contributor',
        'email' => 'new-contributor@example.com',
        'password' => 'SecurePassword123!',
        'role' => 'contributor',
    ]);

    Notification::assertSentTo(
        $user,
        VerifyEmail::class,
    );
});
