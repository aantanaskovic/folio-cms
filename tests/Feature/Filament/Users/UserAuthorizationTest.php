<?php

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows an admin to delete a contributor', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $user = User::factory()->create();
    $user->assignRole('contributor');

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->callAction(TestAction::make('delete')->table($user));

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

it('does not expose the delete action for the currently authenticated user', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->assertActionHidden(TestAction::make('delete')->table($admin));
});

it('does not allow contributors to access the user list', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    Livewire::actingAs($contributor)
        ->test(ListUsers::class)
        ->assertForbidden();
});

it('does not allow users without a role to access the user list', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    Livewire::actingAs($user)
        ->test(ListUsers::class)
        ->assertForbidden();
});

it('redirects guests to the admin login page', function () {
    $this->get('/admin/users/create')
        ->assertRedirect('/admin/login');
});

it('allows an admin to filter users by the admin role', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->filterTable('role', 'admin')
        ->assertCanSeeTableRecords([$admin])
        ->assertCanNotSeeTableRecords([$contributor]);
});

it('shows users with all roles after clearing the role filter', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->filterTable('role', 'contributor')
        ->assertCanSeeTableRecords([$contributor])
        ->assertCanNotSeeTableRecords([$admin])
        ->removeTableFilter('role')
        ->assertCanSeeTableRecords([$admin, $contributor]);
});

it('does not allow contributors to access user creation and edit pages', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $target = User::factory()->create();
    $target->assignRole('contributor');

    $this->actingAs($contributor)
        ->get('/admin/users/create')
        ->assertForbidden();

    $this->actingAs($contributor)
        ->get("/admin/users/{$target->id}/edit")
        ->assertForbidden();
});
