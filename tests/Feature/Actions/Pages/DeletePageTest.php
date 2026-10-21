<?php

use App\Actions\Pages\DeletePage;
use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows a contributor to delete their own draft page', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $page = Page::factory()->for($user, 'author')->create([
        'status' => PageStatus::Draft,
    ]);

    app(DeletePage::class)->handle($user, $page);

    $this->assertDatabaseMissing('pages', [
        'id' => $page->id,
    ]);
});

it('does not allow a contributor to delete another authors draft page', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $author = User::factory()->create();
    $author->assignRole('contributor');

    $page = Page::factory()->for($author, 'author')->create([
        'status' => PageStatus::Draft,
    ]);

    expect(fn() => app(DeletePage::class)->handle($user, $page))
        ->toThrow(AuthorizationException::class);

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
    ]);
});

it('does not allow a contributor to delete a page that is pending review', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $page = Page::factory()->for($user, 'author')->create([
        'status' => PageStatus::Pending,
    ]);

    expect(fn() => app(DeletePage::class)->handle($user, $page))
        ->toThrow(AuthorizationException::class);

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
    ]);
});

it('does not allow a contributor to delete a published page', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $page = Page::factory()->for($user, 'author')->create([
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    expect(fn() => app(DeletePage::class)->handle($user, $page))
        ->toThrow(AuthorizationException::class);

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
    ]);
});

it('does not allow a user without delete permission to delete a page', function () {
    $user = User::factory()->create();

    $page = Page::factory()->create([
        'status' => PageStatus::Draft,
    ]);

    expect(fn() => app(DeletePage::class)->handle($user, $page))
        ->toThrow(AuthorizationException::class);

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
    ]);
});

it('allows an admin with delete permission to delete a page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $author = User::factory()->create();
    $author->assignRole('contributor');

    $page = Page::factory()->for($author, 'author')->create([
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    app(DeletePage::class)->handle($admin, $page);

    $this->assertDatabaseMissing('pages', [
        'id' => $page->id,
    ]);
});
