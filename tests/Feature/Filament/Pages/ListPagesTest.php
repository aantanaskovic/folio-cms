<?php

use App\Enums\PageStatus;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows a contributor to delete their own draft page from the table', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->create([
        'author_id' => $contributor->id,
        'status' => PageStatus::Draft,
    ]);

    Livewire::actingAs($contributor)
        ->test(ListPages::class)
        ->callAction(TestAction::make('delete')->table($page));

    $this->assertDatabaseMissing('pages', [
        'id' => $page->id,
    ]);
});

it('does not expose the delete action for another contributors draft page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $author = User::factory()->create();
    $author->assignRole('contributor');

    $page = Page::factory()->create([
        'author_id' => $author->id,
        'status' => PageStatus::Draft,
    ]);

    Livewire::actingAs($contributor)
        ->test(ListPages::class)
        ->assertActionHidden(TestAction::make('delete')->table($page));
});

it('does not expose the delete action for their own page pending review', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->create([
        'author_id' => $contributor->id,
        'status' => PageStatus::Pending,
    ]);

    Livewire::actingAs($contributor)
        ->test(ListPages::class)
        ->assertActionHidden(TestAction::make('delete')->table($page));
});

it('allows an admin to delete another contributors published page from the table', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $author = User::factory()->create();
    $author->assignRole('contributor');

    $page = Page::factory()->create([
        'author_id' => $author->id,
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    Livewire::actingAs($admin)
        ->test(ListPages::class)
        ->callAction(TestAction::make('delete')->table($page));

    $this->assertDatabaseMissing('pages', [
        'id' => $page->id,
    ]);
});

it('allows a contributor to submit their own draft page for review', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->for($contributor, 'author')->create([
        'status' => PageStatus::Draft,
    ]);

    Livewire::actingAs($contributor)
        ->test(ListPages::class)
        ->callAction(TestAction::make('submitForReview')->table($page));

    expect($page->fresh()->status)->toBe(PageStatus::Pending);
});

it('does not expose the submit for review action for another contributors draft page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $author = User::factory()->create();
    $author->assignRole('contributor');

    $page = Page::factory()->for($author, 'author')->create([
        'status' => PageStatus::Draft,
    ]);

    Livewire::actingAs($contributor)
        ->test(ListPages::class)
        ->assertActionHidden(
            TestAction::make('submitForReview')->table($page)
        );
});

it('does not expose the submit for review action for a page pending review', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->for($contributor, 'author')->create([
        'status' => PageStatus::Pending,
    ]);

    Livewire::actingAs($contributor)
        ->test(ListPages::class)
        ->assertActionHidden(
            TestAction::make('submitForReview')->table($page)
        );
});

it('does not expose the submit for review action for a published page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->for($contributor, 'author')->create([
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    Livewire::actingAs($contributor)
        ->test(ListPages::class)
        ->assertActionHidden(
            TestAction::make('submitForReview')->table($page)
        );
});

it('allows an admin to return a pending page to draft', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $author = User::factory()->create();
    $author->assignRole('contributor');

    $page = Page::factory()->for($author, 'author')->create([
        'status' => PageStatus::Pending,
        'published_at' => null,
    ]);

    Livewire::actingAs($admin)
        ->test(ListPages::class)
        ->callAction(TestAction::make('returnToDraft')->table($page));

    expect($page->fresh()->status)->toBe(PageStatus::Draft)
        ->and($page->fresh()->published_at)->toBeNull();
});

it('does not expose the return to draft action to a contributor', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->create([
        'status' => PageStatus::Pending,
        'published_at' => null,
    ]);

    Livewire::actingAs($contributor)
        ->test(ListPages::class)
        ->assertActionHidden(
            TestAction::make('returnToDraft')->table($page)
        );
});

it('does not expose the return to draft action for a draft page', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $page = Page::factory()->for($admin, 'author')->create([
        'status' => PageStatus::Draft,
    ]);

    Livewire::actingAs($admin)
        ->test(ListPages::class)
        ->assertActionHidden(
            TestAction::make('returnToDraft')->table($page)
        );
});

it('allows an admin to publish a pending page', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $author = User::factory()->create();
    $author->assignRole('contributor');

    $page = Page::factory()->for($author, 'author')->create([
        'status' => PageStatus::Pending,
        'published_at' => null,
    ]);

    Livewire::actingAs($admin)
        ->test(ListPages::class)
        ->callAction(TestAction::make('publish')->table($page));

    expect($page->fresh()->status)->toBe(PageStatus::Published)
        ->and($page->fresh()->published_at)->not->toBeNull();
});

it('does not expose the publish action to a contributor', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->create([
        'status' => PageStatus::Pending,
        'published_at' => null,
    ]);

    Livewire::actingAs($contributor)
        ->test(ListPages::class)
        ->assertActionHidden(
            TestAction::make('publish')->table($page)
        );
});

it('does not expose the publish action for a draft page', function () {
    $admin = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $admin->assignRole('admin');

    $page = Page::factory()->for($admin, 'author')->create([
        'status' => PageStatus::Draft,
    ]);

    Livewire::actingAs($admin)
        ->test(ListPages::class)
        ->assertActionHidden(
            TestAction::make('publish')->table($page)
        );
});
