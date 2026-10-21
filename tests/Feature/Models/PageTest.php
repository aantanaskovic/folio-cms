<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;

it('casts page attributes to their expected types', function () {
    $page = Page::factory()->create([
        'blocks' => [
            ['type' => 'text', 'data' => ['content' => 'About us']],
        ],
        'status' => PageStatus::Published,
        'published_at' => now(),
        'show_in_navigation' => true,
    ]);

    expect($page->blocks)->toBeArray()
        ->and($page->status)->toBe(PageStatus::Published)
        ->and($page->published_at)->toBeInstanceOf(Carbon::class)
        ->and($page->show_in_navigation)->toBeTrue();
});

it('belongs to its author', function () {
    $author = User::factory()->create();

    $page = $author->pages()->create([
        'title' => 'About Us',
        'slug' => 'about-us',
        'blocks' => [],
    ]);

    expect($page->author)->toBeInstanceOf(User::class)
        ->and($page->author->is($author))->toBeTrue();
});

it('allows an author to retrieve their pages', function () {
    $author = User::factory()->create();

    $page = $author->pages()->create([
        'title' => 'About Us',
        'slug' => 'about-us',
        'blocks' => [],
    ]);

    expect($author->pages)->toHaveCount(1)
        ->and($author->pages->first()->is($page))->toBeTrue();
});

it('does not allow mass assignment of the author foreign key', function () {
    expect((new Page)->isFillable('author_id'))->toBeFalse();
});

it('uses the expected database defaults for a new page', function () {
    $author = User::factory()->create();

    $page = $author->pages()->create([
        'title' => 'About Us',
        'slug' => 'about-us',
        'blocks' => [],
    ]);

    $page->refresh();

    expect($page->status)->toBe(PageStatus::Draft)
        ->and($page->show_in_navigation)->toBeFalse()
        ->and($page->published_at)->toBeNull();
});

it('allows optional page attributes to be null', function () {
    $page = Page::factory()->create([
        'excerpt' => null,
        'cover_image' => null,
        'meta_title' => null,
        'meta_description' => null,
        'published_at' => null,
    ]);

    expect($page->excerpt)->toBeNull()
        ->and($page->cover_image)->toBeNull()
        ->and($page->meta_title)->toBeNull()
        ->and($page->meta_description)->toBeNull()
        ->and($page->published_at)->toBeNull();
});

it('requires each page slug to be unique', function () {
    $existingPage = Page::factory()->create();

    expect(
        fn() => Page::factory()->create([
            'slug' => $existingPage->slug,
        ]),
    )->toThrow(QueryException::class);
});
