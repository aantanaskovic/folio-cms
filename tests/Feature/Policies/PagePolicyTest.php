<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows an admin to manage every page', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $page = Page::factory()->create();

    expect($admin->can('viewAny', Page::class))->toBeTrue()
        ->and($admin->can('view', $page))->toBeTrue()
        ->and($admin->can('create', Page::class))->toBeTrue()
        ->and($admin->can('update', $page))->toBeTrue()
        ->and($admin->can('delete', $page))->toBeTrue()
        ->and($admin->can('review', $page))->toBeTrue()
        ->and($admin->can('publish', $page))->toBeTrue();
});

it('allows a contributor to create pages and manage their own draft', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $page = Page::factory()->for($contributor, 'author')->create();

    expect($contributor->can('viewAny', Page::class))->toBeTrue()
        ->and($contributor->can('create', Page::class))->toBeTrue()
        ->and($contributor->can('view', $page))->toBeTrue()
        ->and($contributor->can('update', $page))->toBeTrue()
        ->and($contributor->can('delete', $page))->toBeTrue()
        ->and($contributor->can('submitForReview', $page))->toBeTrue()
        ->and($contributor->can('review', $page))->toBeFalse()
        ->and($contributor->can('publish', $page))->toBeFalse();
});

it('does not allow a contributor to manage another contributor draft', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $otherContributor = User::factory()->create();
    $otherContributor->assignRole('contributor');

    $page = Page::factory()->for($otherContributor, 'author')->create();

    expect($contributor->can('view', $page))->toBeFalse()
        ->and($contributor->can('update', $page))->toBeFalse()
        ->and($contributor->can('delete', $page))->toBeFalse()
        ->and($contributor->can('submitForReview', $page))->toBeFalse();
});

it('allows contributors to view published pages they do not own', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $page = Page::factory()->create([
        'status' => PageStatus::Published,
        'published_at' => now(),
    ]);

    expect($contributor->can('view', $page))->toBeTrue()
        ->and($contributor->can('update', $page))->toBeFalse()
        ->and($contributor->can('delete', $page))->toBeFalse();
});

it('allows contributors to update their own pending pages but not delete or resubmit them', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $page = Page::factory()->for($contributor, 'author')->create([
        'status' => PageStatus::Pending,
    ]);

    expect($contributor->can('view', $page))->toBeTrue()
        ->and($contributor->can('update', $page))->toBeTrue()
        ->and($contributor->can('delete', $page))->toBeFalse()
        ->and($contributor->can('submitForReview', $page))->toBeFalse();
});

it('denies page management to users without the required permissions', function () {
    $user = User::factory()->create();

    $page = Page::factory()->create();

    expect($user->can('viewAny', Page::class))->toBeFalse()
        ->and($user->can('create', Page::class))->toBeFalse()
        ->and($user->can('view', $page))->toBeFalse()
        ->and($user->can('update', $page))->toBeFalse()
        ->and($user->can('delete', $page))->toBeFalse()
        ->and($user->can('review', $page))->toBeFalse()
        ->and($user->can('publish', $page))->toBeFalse();
});

it('does not allow a user without page view permission to view a published page in the admin panel', function () {
    $user = User::factory()->create();

    $page = Page::factory()->create([
        'status' => PageStatus::Published,
        'published_at' => now(),
    ]);

    expect($user->can('view', $page))->toBeFalse();
});
