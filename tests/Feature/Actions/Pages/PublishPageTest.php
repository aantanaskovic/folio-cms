<?php

use App\Actions\Pages\PublishPage;
use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('publishes a page that is pending review', function () {
    $publishedAt = Carbon::parse('2026-01-15 12:00:00');
    Carbon::setTestNow($publishedAt);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $page = Page::factory()->for($admin, 'author')->create([
        'status' => PageStatus::Pending,
        'published_at' => null,
    ]);

    $publishedPage = app(PublishPage::class)->handle($admin, $page);

    expect($publishedPage->status)->toBe(PageStatus::Published)
        ->and($publishedPage->published_at)->not->toBeNull()
        ->and($publishedPage->published_at->toDateTimeString())
        ->toBe($publishedAt->toDateTimeString());

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
        'status' => PageStatus::Published->value,
    ]);
});

it('does not allow a user without publishing permission to publish a page', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $page = Page::factory()->create([
        'status' => PageStatus::Pending,
        'published_at' => null,
    ]);

    expect(fn() => app(PublishPage::class)->handle($contributor, $page))
        ->toThrow(AuthorizationException::class);

    expect($page->fresh()->status)->toBe(PageStatus::Pending)
        ->and($page->fresh()->published_at)->toBeNull();
});

it('does not allow a draft page to be published directly', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $page = Page::factory()->for($admin, 'author')->create([
        'status' => PageStatus::Draft,
        'published_at' => null,
    ]);

    expect(fn() => app(PublishPage::class)->handle($admin, $page))
        ->toThrow(AuthorizationException::class);

    expect($page->fresh()->status)->toBe(PageStatus::Draft)
        ->and($page->fresh()->published_at)->toBeNull();
});

it('does not allow a published page to be published again', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $publishedAt = Carbon::parse('2026-01-14 12:00:00');

    $page = Page::factory()->for($admin, 'author')->create([
        'status' => PageStatus::Published,
        'published_at' => $publishedAt,
    ]);

    expect(fn() => app(PublishPage::class)->handle($admin, $page))
        ->toThrow(AuthorizationException::class);

    expect($page->fresh()->status)->toBe(PageStatus::Published)
        ->and($page->fresh()->published_at->toDateTimeString())
        ->toBe($publishedAt->toDateTimeString());
});
