<?php

use App\Actions\Pages\CreatePage;
use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('creates a draft page for the authorized user', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $page = app(CreatePage::class)->handle($user, [
        'title' => 'My Portfolio',
        'slug' => 'my-portfolio',
        'excerpt' => 'A summary of my portfolio.',
        'blocks' => [
            [
                'type' => 'text',
                'data' => ['content' => 'My portfolio'],
            ],
        ],
    ]);

    expect($page)
        ->toBeInstanceOf(Page::class)
        ->and($page->author_id)->toBe($user->id)
        ->and($page->status)->toBe(PageStatus::Draft)
        ->and($page->published_at)->toBeNull()
        ->and($page->title)->toBe('My Portfolio')
        ->and($page->slug)->toBe('my-portfolio');

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
        'author_id' => $user->id,
        'status' => PageStatus::Draft->value,
    ]);
});

it('does not allow a user without page creation permission to create a page', function () {
    $user = User::factory()->create();

    expect(fn() => app(CreatePage::class)->handle($user, [
        'title' => 'My Portfolio',
        'slug' => 'my-portfolio',
        'blocks' => [
            [
                'type' => 'text',
                'data' => ['content' => 'My portfolio'],
            ],
        ],
    ]))->toThrow(AuthorizationException::class);

    expect(Page::query()->count())->toBe(0);
});

it('does not allow submitted data to override protected page attributes', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $anotherUser = User::factory()->create();

    $page = app(CreatePage::class)->handle($user, [
        'title' => 'My Portfolio',
        'slug' => 'my-portfolio',
        'blocks' => [
            [
                'type' => 'text',
                'data' => ['content' => 'My portfolio'],
            ],
        ],
        'author_id' => $anotherUser->id,
        'status' => PageStatus::Published->value,
        'published_at' => now(),
    ]);

    expect($page->author_id)->toBe($user->id)
        ->and($page->status)->toBe(PageStatus::Draft)
        ->and($page->published_at)->toBeNull();
});

it('rejects invalid page data', function (array $data, bool $hasExistingPage = false) {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    if ($hasExistingPage) {
        Page::factory()->create([
            'slug' => 'existing-page',
        ]);
    }

    expect(fn() => app(CreatePage::class)->handle($user, $data))
        ->toThrow(ValidationException::class);

    expect(Page::query()->count())->toBe($hasExistingPage ? 1 : 0);
})->with([
    'missing title' => [[
        'slug' => 'my-portfolio',
        'blocks' => [
            [
                'type' => 'text',
                'data' => ['content' => 'My portfolio'],
            ],
        ],
    ]],
    'missing slug' => [[
        'title' => 'My Portfolio',
        'blocks' => [],
    ]],
    'duplicate slug' => [[
        'title' => 'Another Page',
        'slug' => 'existing-page',
        'blocks' => [
            [
                'type' => 'text',
                'data' => ['content' => 'My portfolio'],
            ],
        ],
    ], true],
    'invalid blocks structure' => [[
        'title' => 'My Portfolio',
        'slug' => 'my-portfolio',
        'blocks' => 'not-an-array',
    ]],
]);
