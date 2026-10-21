<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::findOrCreate('admin', 'web');
    Role::findOrCreate('contributor', 'web');

    Permission::findOrCreate('pages.view', 'web');
    Permission::findOrCreate('pages.create', 'web');
});

it('redirects guests to login', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('displays the dashboard for a verified contributor', function () {
    $user = User::factory()->create();

    $role = Role::findByName('contributor', 'web');

    $role->givePermissionTo([
        'pages.view',
        'pages.create',
    ]);

    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee("Welcome back, {$user->name}.")
        ->assertSee('Pages')
        ->assertSee('Manage pages')
        ->assertSee('Create page')
        ->assertDontSee('Administration')
        ->assertSee('View website')
        ->assertSee('Sign out');
});

it('displays administration options for an admin', function () {
    $user = User::factory()->create();
    $user->assignRole('admin');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Administration')
        ->assertSee('Open admin panel');
});

it('redirects unverified users to email verification', function () {
    $user = User::factory()->unverified()->create();
    $user->assignRole('contributor');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));
});

it('displays page management links according to the user permissions', function () {
    $user = User::factory()->create();
    $role = Role::findByName('contributor', 'web');

    $role->givePermissionTo([
        Permission::findOrCreate('pages.view', 'web'),
        Permission::findOrCreate('pages.create', 'web'),
    ]);

    $user->assignRole($role);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(route('filament.admin.resources.pages.index'), false)
        ->assertSee(route('filament.admin.resources.pages.create'), false)
        ->assertSee('Pages')
        ->assertSee('Create page');
});

it('does not display page management links without the required permissions', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee(route('filament.admin.resources.pages.index'), false)
        ->assertDontSee(route('filament.admin.resources.pages.create'), false);
});
