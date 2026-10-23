<?php

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows an admin to create a contributor account', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => 'New Contributor',
            'email' => 'new-contributor@example.com',
            'password' => 'SecurePassword123!',
            'role' => 'contributor',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::query()
        ->where('email', 'new-contributor@example.com')
        ->firstOrFail();

    expect($user->name)->toBe('New Contributor')
        ->and($user->hasRole('contributor'))->toBeTrue()
        ->and($user->hasRole('admin'))->toBeFalse()
        ->and(Hash::check('SecurePassword123!', $user->password))->toBeTrue();
});

it('allows an admin to create another administrator', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => 'Second Administrator',
            'email' => 'second-admin@example.com',
            'password' => 'SecurePassword123!',
            'role' => 'admin',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $user = User::query()
        ->where('email', 'second-admin@example.com')
        ->firstOrFail();

    expect($user->hasRole('admin'))->toBeTrue()
        ->and($user->hasRole('contributor'))->toBeFalse();
});

it('requires a name when creating a user', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => '',
            'email' => 'valid@example.com',
            'password' => 'SecurePassword123!',
            'role' => 'contributor',
        ])
        ->call('create')
        ->assertHasFormErrors(['name' => 'required']);
});

it('requires a valid email address when creating a user', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => 'New Contributor',
            'email' => 'invalid-email',
            'password' => 'SecurePassword123!',
            'role' => 'contributor',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'email']);
});

it('requires a unique email address when creating a user', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    User::factory()->create([
        'email' => 'existing@example.com',
    ]);

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => 'Another User',
            'email' => 'existing@example.com',
            'password' => 'SecurePassword123!',
            'role' => 'contributor',
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

it('requires a password when creating a user', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => 'New Contributor',
            'email' => 'new@example.com',
            'password' => '',
            'role' => 'contributor',
        ])
        ->call('create')
        ->assertHasFormErrors(['password' => 'required']);
});

it('requires a role when creating a user', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => 'New Contributor',
            'email' => 'new@example.com',
            'password' => 'SecurePassword123!',
            'role' => null,
        ])
        ->call('create')
        ->assertHasFormErrors(['role' => 'required']);
});

it('does not allow an admin to assign an unsupported role', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(CreateUser::class)
        ->fillForm([
            'name' => 'New User',
            'email' => 'new@example.com',
            'password' => 'SecurePassword123!',
            'role' => 'super-admin',
        ])
        ->call('create')
        ->assertHasFormErrors(['role']);

    $this->assertDatabaseMissing('users', [
        'email' => 'new@example.com',
    ]);
});

it('does not allow a contributor to create users', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    Livewire::actingAs($contributor)
        ->test(CreateUser::class)
        ->assertForbidden();
});
