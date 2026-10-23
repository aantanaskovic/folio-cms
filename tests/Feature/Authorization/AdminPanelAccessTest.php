<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Facades\Filament;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows an admin to access the Filament panel', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $panel = Filament::getPanel('admin');

    expect($admin->canAccessPanel($panel))->toBeTrue();

    $this->actingAs($admin);

    $response = $this->get('/admin');

    expect($response->status())->toBe(
        200,
        "HTTP status: {$response->status()}\n"
            . 'Redirect: ' . ($response->headers->get('Location') ?? 'none') . "\n"
            . 'Response body: ' . $response->getContent(),
    );
});

it('allows a contributor access to the Filament panel', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    expect($contributor->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();

    $this->actingAs($contributor)
        ->get('/admin')
        ->assertOk();
});

it('denies panel access to a user without a role', function () {
    $user = User::factory()->create();

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('redirects guests to the Filament login page', function () {
    $this->get('/admin')
        ->assertRedirect('/admin/login');
});

it('does not allow an unverified admin to access the Filament dashboard', function () {
    $admin = User::factory()->create([
        'email_verified_at' => null,
    ]);

    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertRedirect();
});

it('denies a contributor direct access to another contributors draft page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $author = User::factory()->create();

    $page = Page::factory()->create([
        'author_id' => $author->id,
        'status' => PageStatus::Draft,
    ]);

    $this->actingAs($contributor)
        ->get("/admin/pages/{$page->id}")
        ->assertForbidden();
});

it('denies a contributor direct access to edit another contributors draft page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $author = User::factory()->create();

    $page = Page::factory()->create([
        'author_id' => $author->id,
        'status' => PageStatus::Draft,
    ]);

    $this->actingAs($contributor)
        ->get("/admin/pages/{$page->id}/edit")
        ->assertForbidden();
});

it('allows a contributor direct access to edit their own draft page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->create([
        'author_id' => $contributor->id,
        'status' => PageStatus::Draft,
    ]);

    $this->actingAs($contributor)
        ->get("/admin/pages/{$page->id}/edit")
        ->assertOk();
});

it('allows a contributor direct access to view their own draft page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->create([
        'author_id' => $contributor->id,
        'status' => PageStatus::Draft,
    ]);

    $this->actingAs($contributor)
        ->get("/admin/pages/{$page->id}")
        ->assertOk();
});

it('allows a contributor direct access to view their own published page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->create([
        'author_id' => $contributor->id,
        'status' => PageStatus::Published,
        'published_at' => now(),
    ]);

    $this->actingAs($contributor)
        ->get("/admin/pages/{$page->id}")
        ->assertOk();
});

it('denies a contributor direct access to edit their own published page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->create([
        'author_id' => $contributor->id,
        'status' => PageStatus::Published,
        'published_at' => now(),
    ]);

    $this->actingAs($contributor)
        ->get("/admin/pages/{$page->id}/edit")
        ->assertForbidden();
});

it('allows a contributor direct access to view another contributors published page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $anotherContributor = User::factory()->create();

    $page = Page::factory()->create([
        'author_id' => $anotherContributor->id,
        'status' => PageStatus::Published,
        'published_at' => now(),
    ]);

    $this->actingAs($contributor)
        ->get("/admin/pages/{$page->id}")
        ->assertOk();
});

it('denies a contributor direct access to edit another contributors published page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $anotherContributor = User::factory()->create();

    $page = Page::factory()->create([
        'author_id' => $anotherContributor->id,
        'status' => PageStatus::Published,
        'published_at' => now(),
    ]);

    $this->actingAs($contributor)
        ->get("/admin/pages/{$page->id}/edit")
        ->assertForbidden();
});

it('denies a contributor direct access to view another contributors pending page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $anotherContributor = User::factory()->create();

    $page = Page::factory()->create([
        'author_id' => $anotherContributor->id,
        'status' => PageStatus::Pending,
        'published_at' => null,
    ]);

    $this->actingAs($contributor)
        ->get("/admin/pages/{$page->id}")
        ->assertForbidden();
});

it('allows a contributor direct access to edit their own pending page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->create([
        'author_id' => $contributor->id,
        'status' => PageStatus::Pending,
        'published_at' => null,
    ]);

    $this->actingAs($contributor)
        ->get("/admin/pages/{$page->id}/edit")
        ->assertOk();
});

it('denies a contributor direct access to edit another contributors pending page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $anotherContributor = User::factory()->create();

    $page = Page::factory()->create([
        'author_id' => $anotherContributor->id,
        'status' => PageStatus::Pending,
        'published_at' => null,
    ]);

    $this->actingAs($contributor)
        ->get("/admin/pages/{$page->id}/edit")
        ->assertForbidden();
});

it('provides navigation links to the dashboard and public website in the admin user menu', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Dashboard')
        ->assertSee(route('dashboard'), false)
        ->assertSee('View website')
        ->assertSee(route('home'), false);
});

it('allows panel access based on the administration permission without a specific role', function () {
    $role = Role::create([
        'name' => 'reviewer',
        'guard_name' => 'web',
    ]);

    $role->givePermissionTo('pages.administration');

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $user->assignRole($role);

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeTrue();

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk();
});

it('denies panel access to a user whose role has no administration permission', function () {
    $role = Role::create([
        'name' => 'restricted',
        'guard_name' => 'web',
    ]);

    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $user->assignRole($role);

    expect($user->canAccessPanel(Filament::getPanel('admin')))->toBeFalse();

    $this->actingAs($user)
        ->get('/admin')
        ->assertForbidden();
});

it('does not grant user administration access to a contributor', function () {
    $contributor = User::factory()->create();

    $contributor->assignRole('contributor');

    expect($contributor->can('pages.administration'))->toBeTrue()
        ->and($contributor->can('users.administration'))->toBeFalse();
});
