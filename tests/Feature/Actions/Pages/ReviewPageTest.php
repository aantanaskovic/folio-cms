<?php

use App\Actions\Pages\ReviewPage;
use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows an authorized reviewer to approve a pending page for publication', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $page = Page::factory()->for($admin, 'author')->create([
        'status' => PageStatus::Pending,
        'published_at' => null,
    ]);

    $reviewedPage = app(ReviewPage::class)->handle($admin, $page, true);

    expect($reviewedPage->status)->toBe(PageStatus::Pending)
        ->and($reviewedPage->published_at)->toBeNull();

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
        'status' => PageStatus::Pending->value,
        'published_at' => null,
    ]);
});

it('returns a pending page to draft when the reviewer requests changes', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $page = Page::factory()->for($admin, 'author')->create([
        'status' => PageStatus::Pending,
        'published_at' => null,
    ]);

    $reviewedPage = app(ReviewPage::class)->handle($admin, $page, false);

    expect($reviewedPage->status)->toBe(PageStatus::Draft)
        ->and($reviewedPage->published_at)->toBeNull();

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
        'status' => PageStatus::Draft->value,
        'published_at' => null,
    ]);
});

it('does not allow a user without review permission to review a page', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $page = Page::factory()->create([
        'status' => PageStatus::Pending,
        'published_at' => null,
    ]);

    expect(fn() => app(ReviewPage::class)->handle($contributor, $page, true))
        ->toThrow(AuthorizationException::class);

    expect($page->fresh()->status)->toBe(PageStatus::Pending)
        ->and($page->fresh()->published_at)->toBeNull();
});

it('does not allow a reviewer to review a page that is not pending', function (PageStatus $status) {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $page = Page::factory()->for($admin, 'author')->create([
        'status' => $status,
        'published_at' => $status === PageStatus::Published ? now() : null,
    ]);

    expect(fn() => app(ReviewPage::class)->handle($admin, $page, true))
        ->toThrow(AuthorizationException::class);

    expect($page->fresh()->status)->toBe($status);
})->with([
    'draft page' => [PageStatus::Draft],
    'published page' => [PageStatus::Published],
]);
