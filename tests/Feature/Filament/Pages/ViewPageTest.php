<?php

use App\Enums\PageStatus;
use App\Filament\Resources\Pages\Pages\ViewPage;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Filament\Actions\EditAction;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('displays page details to a contributor who can view the page', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $page = Page::factory()
        ->for($contributor, 'author')
        ->create([
            'title' => 'My Portfolio',
            'slug' => 'my-portfolio',
            'excerpt' => 'A portfolio page excerpt.',
            'status' => PageStatus::Draft,
        ]);

    Livewire::actingAs($contributor)
        ->test(ViewPage::class, ['record' => $page->getRouteKey()])
        ->assertOk()
        ->assertSee('My Portfolio')
        ->assertSee('my-portfolio')
        ->assertSee('A portfolio page excerpt.')
        ->assertSee('Draft');
});

it('displays SEO and navigation details', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $page = Page::factory()
        ->for($admin, 'author')
        ->create([
            'meta_title' => 'Portfolio SEO title',
            'meta_description' => 'Portfolio SEO description.',
            'show_in_navigation' => true,
        ]);

    Livewire::actingAs($admin)
        ->test(ViewPage::class, ['record' => $page->getRouteKey()])
        ->assertOk()
        ->assertSee('Portfolio SEO title')
        ->assertSee('Portfolio SEO description.');
});

it('shows the edit action to a contributor for their own draft', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $page = Page::factory()
        ->for($contributor, 'author')
        ->create([
            'status' => PageStatus::Draft,
        ]);

    Livewire::actingAs($contributor)
        ->test(ViewPage::class, ['record' => $page->getRouteKey()])
        ->assertOk()
        ->assertActionVisible(EditAction::class);
});

it('hides the edit action from a contributor for a published page', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $page = Page::factory()
        ->for($contributor, 'author')
        ->create([
            'status' => PageStatus::Published,
            'published_at' => now(),
        ]);

    Livewire::actingAs($contributor)
        ->test(ViewPage::class, ['record' => $page->getRouteKey()])
        ->assertOk()
        ->assertActionHidden(EditAction::class);
});

it('displays structured content blocks on the page view', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $page = Page::factory()
        ->for($admin, 'author')
        ->create([
            'blocks' => [
                [
                    'type' => 'hero',
                    'data' => [
                        'heading' => 'Welcome to my portfolio',
                        'subheading' => 'Selected work and case studies',
                    ],
                ],
                [
                    'type' => 'text',
                    'data' => [
                        'content' => 'A detailed project overview.',
                    ],
                ],
                [
                    'type' => 'gallery',
                    'data' => [
                        'images' => [
                            [
                                'url' => 'gallery/project-one.jpg',
                                'alt' => 'Project dashboard screenshot',
                            ],
                        ],
                    ],
                ],
                [
                    'type' => 'cta',
                    'data' => [
                        'heading' => 'Interested in working together?',
                        'label' => 'Contact me',
                        'url' => 'https://example.com/contact',
                    ],
                ],
            ],
        ]);

    Livewire::actingAs($admin)
        ->test(ViewPage::class, ['record' => $page->getRouteKey()])
        ->assertOk()
        ->assertSee('Welcome to my portfolio')
        ->assertSee('Content blocks')
        ->assertSee('Selected work and case studies')
        ->assertSee('A detailed project overview.')
        ->assertSee('Project dashboard screenshot')
        ->assertSee('Interested in working together?')
        ->assertSee('Contact me')
        ->assertSee('https://example.com/contact');
});

it('does not allow a contributor to view another contributors draft page directly', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $author = User::factory()->create();
    $author->assignRole('contributor');

    $page = Page::factory()
        ->for($author, 'author')
        ->create([
            'status' => PageStatus::Draft,
        ]);

    Livewire::actingAs($contributor)
        ->test(ViewPage::class, ['record' => $page->getRouteKey()])
        ->assertForbidden();
});

it('does not allow a contributor to view another contributors pending page directly', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $author = User::factory()->create();
    $author->assignRole('contributor');

    $page = Page::factory()
        ->for($author, 'author')
        ->create([
            'status' => PageStatus::Pending,
        ]);

    Livewire::actingAs($contributor)
        ->test(ViewPage::class, ['record' => $page->getRouteKey()])
        ->assertForbidden();
});

it('does not allow a user without page view permission to access the page view', function () {
    $contributor = User::factory()->create();
    $contributor->assignRole('contributor');

    $page = Page::factory()
        ->for($contributor, 'author')
        ->create([
            'status' => PageStatus::Draft,
        ]);

    Role::findByName('contributor')->revokePermissionTo('pages.view');

    expect($contributor->can('view', $page))->toBeFalse();

    Livewire::actingAs($contributor)
        ->test(ViewPage::class, ['record' => $page->getRouteKey()])
        ->assertForbidden();
});
