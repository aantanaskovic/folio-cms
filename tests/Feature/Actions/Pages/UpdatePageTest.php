<?php

use App\Actions\Pages\UpdatePage;
use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('updates the authors draft page', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $page = Page::factory()->for($user, 'author')->create([
        'title' => 'Original Title',
        'slug' => 'original-title',
        'status' => PageStatus::Draft,
        'published_at' => null,
    ]);

    $updatedPage = app(UpdatePage::class)->handle($user, $page, [
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'excerpt' => 'Updated excerpt.',
        'blocks' => [
            [
                'type' => 'text',
                'data' => ['content' => 'Updated content'],
            ],
        ],
    ]);

    expect($updatedPage->title)->toBe('Updated Title')
        ->and($updatedPage->slug)->toBe('updated-title')
        ->and($updatedPage->excerpt)->toBe('Updated excerpt.')
        ->and($updatedPage->status)->toBe(PageStatus::Draft)
        ->and($updatedPage->author_id)->toBe($user->id);

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
        'title' => 'Updated Title',
        'slug' => 'updated-title',
    ]);
});

it('does not allow a contributor to update another authors page', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $author = User::factory()->create();
    $author->assignRole('contributor');

    $page = Page::factory()->for($author, 'author')->create([
        'status' => PageStatus::Draft,
    ]);

    expect(fn() => app(UpdatePage::class)->handle($user, $page, [
        'title' => 'Unauthorized Update',
        'slug' => 'unauthorized-update',
        'blocks' => [],
    ]))->toThrow(AuthorizationException::class);

    expect($page->fresh()->title)->toBe($page->title);
});

it('does not allow a user without update permission to update a page', function () {
    $user = User::factory()->create();

    $page = Page::factory()->create([
        'status' => PageStatus::Draft,
    ]);

    expect(fn() => app(UpdatePage::class)->handle($user, $page, [
        'title' => 'Unauthorized Update',
        'slug' => 'unauthorized-update',
        'blocks' => [],
    ]))->toThrow(AuthorizationException::class);

    expect($page->fresh()->title)->toBe($page->title);
});

it('does not allow a contributor to update a published page', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $page = Page::factory()->for($user, 'author')->create([
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    expect(fn() => app(UpdatePage::class)->handle($user, $page, [
        'title' => 'Changed Published Page',
        'slug' => 'changed-published-page',
        'blocks' => [],
    ]))->toThrow(AuthorizationException::class);

    expect($page->fresh()->title)->toBe($page->title)
        ->and($page->fresh()->status)->toBe(PageStatus::Published);
});

it('does not allow submitted data to override protected page attributes', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $anotherUser = User::factory()->create();

    $page = Page::factory()->for($user, 'author')->create([
        'status' => PageStatus::Draft,
        'published_at' => null,
    ]);

    $updatedPage = app(UpdatePage::class)->handle($user, $page, [
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'blocks' => [],
        'author_id' => $anotherUser->id,
        'status' => PageStatus::Published->value,
        'published_at' => now(),
    ]);

    expect($updatedPage->author_id)->toBe($user->id)
        ->and($updatedPage->status)->toBe(PageStatus::Draft)
        ->and($updatedPage->published_at)->toBeNull();
});

it('rejects invalid page data', function (array $data, bool $hasExistingPage = false) {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    if ($hasExistingPage) {
        Page::factory()->create([
            'slug' => 'another-existing-page',
        ]);
    }

    $page = Page::factory()->for($user, 'author')->create([
        'title' => 'Original Title',
        'slug' => 'original-title',
        'status' => PageStatus::Draft,
    ]);

    expect(fn() => app(UpdatePage::class)->handle($user, $page, $data))
        ->toThrow(ValidationException::class);

    expect($page->fresh()->title)->toBe('Original Title')
        ->and($page->fresh()->slug)->toBe('original-title');
})->with([
    'missing title' => [[
        'slug' => 'updated-title',
        'blocks' => [],
    ]],
    'missing slug' => [[
        'title' => 'Updated Title',
        'blocks' => [],
    ]],
    'duplicate slug' => [[
        'title' => 'Updated Title',
        'slug' => 'another-existing-page',
        'blocks' => [],
    ], true],
    'invalid blocks structure' => [[
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'blocks' => 'not-an-array',
    ]],

    'block without type' => [[
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'blocks' => [
            ['data' => ['content' => 'Some content']],
        ],
    ]],
    'block without data' => [[
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'blocks' => [
            ['type' => 'text'],
        ],
    ]],
    'block data is not an array' => [[
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'blocks' => [
            ['type' => 'text', 'data' => 'Invalid content'],
        ],
    ]],
    'block type is not a string' => [[
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'blocks' => [
            ['type' => ['text'], 'data' => ['content' => 'Some content']],
        ],
    ]],
    'blocks contain a scalar item' => [[
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'blocks' => [
            'not-a-block',
        ],
    ]],
]);

it('updates whether the page appears in public navigation', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $page = Page::factory()->for($user, 'author')->create([
        'status' => PageStatus::Draft,
        'show_in_navigation' => false,
    ]);

    $updatedPage = app(UpdatePage::class)->handle($user, $page, [
        'title' => $page->title,
        'slug' => $page->slug,
        'blocks' => $page->blocks,
        'show_in_navigation' => true,
    ]);

    expect($updatedPage->show_in_navigation)->toBeTrue();

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
        'show_in_navigation' => true,
    ]);
});

it('allows disabling a page in public navigation', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $page = Page::factory()->for($user, 'author')->create([
        'status' => PageStatus::Draft,
        'show_in_navigation' => true,
    ]);

    $updatedPage = app(UpdatePage::class)->handle($user, $page, [
        'title' => $page->title,
        'slug' => $page->slug,
        'blocks' => $page->blocks,
        'show_in_navigation' => false,
    ]);

    expect($updatedPage->show_in_navigation)->toBeFalse();

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
        'show_in_navigation' => false,
    ]);
});
