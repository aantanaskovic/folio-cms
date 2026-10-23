<?php

use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows an admin to view the users table', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->assertCanSeeTableRecords([$contributor]);
});

it('displays user names and email addresses in the table', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $contributor = User::factory()->create([
        'name' => 'Portfolio Contributor',
        'email' => 'contributor@example.com',
    ]);

    $contributor->assignRole('contributor');

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->assertCanSeeTableRecords([$contributor])
        ->assertSee('Portfolio Contributor')
        ->assertSee('contributor@example.com');
});

it('allows an admin to search users by name', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $matchingUser = User::factory()->create([
        'name' => 'Matching Contributor',
    ]);

    $matchingUser->assignRole('contributor');

    $otherUser = User::factory()->create([
        'name' => 'Different Contributor',
    ]);

    $otherUser->assignRole('contributor');

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->searchTable('Matching Contributor')
        ->assertCanSeeTableRecords([$matchingUser])
        ->assertCanNotSeeTableRecords([$otherUser]);
});

it('allows an admin to search users by email', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $matchingUser = User::factory()->create([
        'email' => 'specific@example.com',
    ]);

    $matchingUser->assignRole('contributor');

    $otherUser = User::factory()->create([
        'email' => 'another@example.com',
    ]);

    $otherUser->assignRole('contributor');

    Livewire::actingAs($admin)
        ->test(ListUsers::class)
        ->searchTable('specific@example.com')
        ->assertCanSeeTableRecords([$matchingUser])
        ->assertCanNotSeeTableRecords([$otherUser]);
});

it('allows an admin to filter users by role', function () {
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
        ->assertCanNotSeeTableRecords([$admin]);
});

it('does not show users without the required view permission to a contributor', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    Livewire::actingAs($contributor)
        ->test(ListUsers::class)
        ->assertForbidden();
});
