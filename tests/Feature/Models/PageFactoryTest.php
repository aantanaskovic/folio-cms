<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\QueryException;

it('creates a page with valid default attributes', function () {
    $page = Page::factory()->create();

    expect($page->exists)->toBeTrue()
        ->and($page->author)->toBeInstanceOf(User::class)
        ->and($page->title)->toBeString()->not->toBeEmpty()
        ->and($page->slug)->toBeString()->not->toBeEmpty()
        ->and($page->excerpt)->toBeString()->not->toBeEmpty()
        ->and($page->blocks)->toBeArray()
        ->and($page->status)->toBe(PageStatus::Draft)
        ->and($page->published_at)->toBeNull()
        ->and($page->cover_image)->toBeNull()
        ->and($page->meta_title)->toBeNull()
        ->and($page->meta_description)->toBeNull()
        ->and($page->show_in_navigation)->toBeFalse();
});

it('creates unique slugs for multiple pages', function () {
    $pages = Page::factory()->count(10)->create();

    expect($pages->pluck('slug')->unique())->toHaveCount(10);
});

it('requires an existing author', function () {
    Page::factory()->create([
        'author_id' => 999999999,
    ]);
})->throws(QueryException::class);

it('prevents deleting an author who owns pages', function () {
    $author = User::factory()->create();

    $author->pages()->create([
        'title' => 'About Us',
        'slug' => 'about-us',
        'blocks' => [],
    ]);

    $author->delete();
})->throws(QueryException::class);

it('enforces slug uniqueness in the database', function () {
    $page = Page::factory()->create();

    Page::factory()->create([
        'slug' => $page->slug,
    ]);
})->throws(QueryException::class);

it('persists page blocks as JSON and restores them as an array', function () {
    $blocks = [
        [
            'type' => 'hero',
            'data' => [
                'heading' => 'Welcome',
                'subheading' => 'Our website',
            ],
        ],
        [
            'type' => 'text',
            'data' => [
                'content' => 'About us',
            ],
        ],
    ];

    $page = Page::factory()->create([
        'blocks' => $blocks,
    ]);

    $reloadedPage = Page::findOrFail($page->id);

    expect($reloadedPage->blocks)->toBe($blocks);
});

it('allows each supported status to be persisted', function (
    PageStatus $status,
) {
    $page = Page::factory()->create([
        'status' => $status,
    ]);

    expect($page->fresh()->status)->toBe($status);
})->with([
    'draft' => [PageStatus::Draft],
    'pending' => [PageStatus::Pending],
    'published' => [PageStatus::Published],
]);
