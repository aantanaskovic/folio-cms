<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('seeds the required roles and permissions through the main database seeder', function () {
    Artisan::call('db:seed');

    expect(Role::where('name', 'admin')->where('guard_name', 'web')->exists())
        ->toBeTrue()
        ->and(Role::where('name', 'contributor')->where('guard_name', 'web')->exists())
        ->toBeTrue()
        ->and(Permission::where('name', 'pages.publish')->where('guard_name', 'web')->exists())
        ->toBeTrue()
        ->and(Permission::where('name', 'users.manage')->where('guard_name', 'web')->exists())
        ->toBeTrue();
});

it('seeds verified demo users with their expected roles', function () {
    Artisan::call('db:seed');

    $admin = User::where('email', 'admin@folio-cms.test')->firstOrFail();
    $contributor = User::where('email', 'contributor@folio-cms.test')->firstOrFail();
    $secondContributor = User::where('email', 'contributor2@folio-cms.test')->firstOrFail();

    expect($admin->hasRole('admin'))
        ->toBeTrue()
        ->and($contributor->hasRole('contributor'))
        ->toBeTrue()
        ->and($secondContributor->hasRole('contributor'))
        ->toBeTrue()
        ->and($admin->email_verified_at)
        ->not->toBeNull()
        ->and($contributor->email_verified_at)
        ->not->toBeNull()
        ->and($secondContributor->email_verified_at)
        ->not->toBeNull();
});

it('seeds portfolio pages with different statuses and correct authors', function () {
    Artisan::call('db:seed');

    $publishedPage = Page::where('slug', 'creative-portfolio')->firstOrFail();
    $hiddenPage = Page::where('slug', 'about-the-studio')->firstOrFail();
    $draftPage = Page::where('slug', 'brand-refresh-case-study')->firstOrFail();
    $pendingPage = Page::where('slug', 'e-commerce-redesign')->firstOrFail();
    $scheduledPage = Page::where('slug', 'upcoming-case-study')->firstOrFail();

    expect($publishedPage->status)
        ->toBe(PageStatus::Published)
        ->and($publishedPage->published_at)
        ->not->toBeNull()
        ->and($publishedPage->show_in_navigation)
        ->toBeTrue()
        ->and($publishedPage->author->email)
        ->toBe('contributor@folio-cms.test')
        ->and($hiddenPage->status)
        ->toBe(PageStatus::Published)
        ->and($hiddenPage->show_in_navigation)
        ->toBeFalse()
        ->and($hiddenPage->author->email)
        ->toBe('contributor2@folio-cms.test')
        ->and($draftPage->status)
        ->toBe(PageStatus::Draft)
        ->and($draftPage->published_at)
        ->toBeNull()
        ->and($pendingPage->status)
        ->toBe(PageStatus::Pending)
        ->and($pendingPage->published_at)
        ->toBeNull()
        ->and($scheduledPage->status)
        ->toBe(PageStatus::Published)
        ->and($scheduledPage->published_at->isFuture())
        ->toBeTrue();
});

it('can run the main database seeder repeatedly without creating duplicates', function () {
    Artisan::call('db:seed');
    Artisan::call('db:seed');

    expect(Role::where('guard_name', 'web')->count())
        ->toBe(2)
        ->and(Permission::where('guard_name', 'web')->count())
        ->toBe(12)
        ->and(User::whereIn('email', [
            'admin@folio-cms.test',
            'contributor@folio-cms.test',
            'contributor2@folio-cms.test',
        ])->count())
        ->toBe(3)
        ->and(Page::whereIn('slug', [
            'creative-portfolio',
            'selected-projects',
            'about-the-studio',
            'brand-refresh-case-study',
            'e-commerce-redesign',
            'upcoming-case-study',
        ])->count())
        ->toBe(6);
});
