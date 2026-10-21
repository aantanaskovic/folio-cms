<?php

use App\Actions\Pages\SubmitPageForReview;
use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Auth\Access\AuthorizationException;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('submits the authors draft page for review', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $page = Page::factory()->for($user, 'author')->create([
        'status' => PageStatus::Draft,
        'published_at' => null,
    ]);

    $submittedPage = app(SubmitPageForReview::class)->handle($user, $page);

    expect($submittedPage->status)->toBe(PageStatus::Pending)
        ->and($submittedPage->published_at)->toBeNull();

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
        'status' => PageStatus::Pending->value,
        'published_at' => null,
    ]);
});

it('does not allow a user to submit another authors page for review', function () {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $author = User::factory()->create();
    $author->assignRole('contributor');

    $page = Page::factory()->for($author, 'author')->create([
        'status' => PageStatus::Draft,
    ]);

    expect(fn() => app(SubmitPageForReview::class)->handle($user, $page))
        ->toThrow(AuthorizationException::class);

    expect($page->fresh()->status)->toBe(PageStatus::Draft);
});

it('does not allow a user without submission permission to submit a page', function () {
    $user = User::factory()->create();

    $page = Page::factory()->for($user, 'author')->create([
        'status' => PageStatus::Draft,
    ]);

    expect(fn() => app(SubmitPageForReview::class)->handle($user, $page))
        ->toThrow(AuthorizationException::class);

    expect($page->fresh()->status)->toBe(PageStatus::Draft);
});

it('does not allow a page that is not a draft to be submitted for review', function (PageStatus $status) {
    $user = User::factory()->create();
    $user->assignRole('contributor');

    $page = Page::factory()->for($user, 'author')->create([
        'status' => $status,
        'published_at' => $status === PageStatus::Published ? now() : null,
    ]);

    expect(fn() => app(SubmitPageForReview::class)->handle($user, $page))
        ->toThrow(AuthorizationException::class);

    expect($page->fresh()->status)->toBe($status);
})->with([
    'pending page' => [PageStatus::Pending],
    'published page' => [PageStatus::Published],
]);
