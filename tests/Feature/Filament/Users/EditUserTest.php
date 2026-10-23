<?php

use App\Filament\Resources\Users\Pages\EditUser;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows an admin to update a users name and email', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $user = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
    ]);

    $user->assignRole('contributor');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
            'role' => 'contributor',
            'password' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
    ]);
});

it('allows an admin to promote a contributor to admin', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('contributor');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'admin',
            'password' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->hasRole('admin'))->toBeTrue()
        ->and($user->fresh()->hasRole('contributor'))->toBeFalse();
});

it('allows an admin to change another admins role to contributor when another admin remains', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $anotherAdmin = User::factory()->create();
    $anotherAdmin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $anotherAdmin->getRouteKey()])
        ->fillForm([
            'name' => $anotherAdmin->name,
            'email' => $anotherAdmin->email,
            'role' => 'contributor',
            'password' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($anotherAdmin->fresh()->hasRole('contributor'))->toBeTrue()
        ->and($anotherAdmin->fresh()->hasRole('admin'))->toBeFalse();
});

it('preserves the existing password when the password field is empty', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $user = User::factory()->create([
        'password' => 'ExistingPassword123!',
    ]);

    $user->assignRole('contributor');

    $originalPasswordHash = $user->password;

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'contributor',
            'password' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($user->fresh()->password)->toBe($originalPasswordHash)
        ->and(Hash::check('ExistingPassword123!', $user->fresh()->password))->toBeTrue();
});

it('allows an admin to change a users password', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('contributor');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'contributor',
            'password' => 'NewSecurePassword123!',
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(Hash::check('NewSecurePassword123!', $user->fresh()->password))->toBeTrue();
});

it('allows a user to retain their own email address when editing their record', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $admin->getRouteKey()])
        ->fillForm([
            'name' => 'Updated Admin',
            'email' => $admin->email,
            'role' => 'admin',
            'password' => '',
        ])
        ->call('save')
        ->assertHasNoFormErrors();
});

it('rejects an email address already assigned to another user', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $user = User::factory()->create([
        'email' => 'user@example.com',
    ]);

    $user->assignRole('contributor');

    User::factory()->create([
        'email' => 'already-used@example.com',
    ]);

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => $user->name,
            'email' => 'already-used@example.com',
            'role' => 'contributor',
            'password' => '',
        ])
        ->call('save')
        ->assertHasFormErrors(['email' => 'unique']);
});

it('does not allow a contributor to edit users', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $user = User::factory()->create();
    $user->assignRole('contributor');

    Livewire::actingAs($contributor)
        ->test(EditUser::class, ['record' => $user->getRouteKey()])
        ->assertForbidden();
});

it('does not allow an admin to delete their own account from the edit page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $admin->getRouteKey()])
        ->assertActionHidden(TestAction::make('delete'));

    expect(User::query()->find($admin->id))->not->toBeNull();
});

it('allows an admin to delete another admin when the acting admin remains', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $anotherAdmin = User::factory()->create();
    $anotherAdmin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $anotherAdmin->getRouteKey()])
        ->callAction(TestAction::make('delete'));

    expect(User::query()->find($anotherAdmin->id))->toBeNull()
        ->and(User::query()->find($admin->id))->not->toBeNull();
});

it('allows an admin to delete another user from the edit page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $contributor->getRouteKey()])
        ->callAction(TestAction::make('delete'));

    expect(User::query()->find($contributor->id))->toBeNull();
});

it('rejects an invalid email address', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('contributor');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => $user->name,
            'email' => 'not-an-email',
            'role' => 'contributor',
            'password' => '',
        ])
        ->call('save')
        ->assertHasFormErrors(['email' => 'email']);

    expect($user->fresh()->email)->toBe($user->email);
});

it('rejects an empty name', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('contributor');

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => '',
            'email' => $user->email,
            'role' => 'contributor',
            'password' => '',
        ])
        ->call('save')
        ->assertHasFormErrors(['name' => 'required']);

    expect($user->fresh()->name)->toBe($user->name);
});

it('rejects a password shorter than eight characters', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('contributor');

    $originalPassword = $user->password;

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $user->getRouteKey()])
        ->fillForm([
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'contributor',
            'password' => 'short',
        ])
        ->call('save')
        ->assertHasFormErrors(['password' => 'min']);

    expect($user->fresh()->password)->toBe($originalPassword);
});

it('loads the assigned role into the edit form', function (string $role) {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole($role);

    Livewire::actingAs($admin)
        ->test(EditUser::class, ['record' => $user->getRouteKey()])
        ->assertSchemaStateSet([
            'role' => $role,
        ]);
})->with([
    'administrator' => 'admin',
    'contributor' => 'contributor',
]);
