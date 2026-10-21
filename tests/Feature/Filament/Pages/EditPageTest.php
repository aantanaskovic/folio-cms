<?php

use App\Enums\PageStatus;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

it('allows a contributor to update their own draft page through Filament', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->for($contributor, 'author')->create([
        'title' => 'Original Title',
        'slug' => 'original-title',
        'status' => PageStatus::Draft,
    ]);

    Livewire::actingAs($contributor)
        ->test(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'excerpt' => 'Updated excerpt.',
            'blocks' => [
                [
                    'type' => 'text',
                    'data' => ['content' => 'Updated content'],
                ],
            ],
            'cover_image' => null,
            'meta_title' => 'Updated SEO title',
            'meta_description' => 'Updated SEO description.',
            'show_in_navigation' => true,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('pages', [
        'id' => $page->id,
        'title' => 'Updated Title',
        'slug' => 'updated-title',
        'excerpt' => 'Updated excerpt.',
        'author_id' => $contributor->id,
        'status' => PageStatus::Draft->value,
    ]);
});

it('does not allow a contributor to edit another contributors draft page', function () {
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
        ->test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertForbidden();

    expect($page->fresh()->author_id)->toBe($author->id);
});

it('does not allow a contributor to edit their own published page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->for($contributor, 'author')->create([
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    Livewire::actingAs($contributor)
        ->test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertForbidden();
});

it('does not allow a contributor to edit another contributors pending page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $author = User::factory()->create();
    $author->assignRole('contributor');

    $page = Page::factory()->for($author, 'author')->create([
        'status' => PageStatus::Pending,
    ]);

    Livewire::actingAs($contributor)
        ->test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertForbidden();
});

it('does not allow a user without update permission to edit a page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    Role::findByName('contributor')->revokePermissionTo('pages.update');

    $page = Page::factory()->for($contributor, 'author')->create([
        'status' => PageStatus::Draft,
    ]);

    Livewire::actingAs($contributor)
        ->test(EditPage::class, ['record' => $page->getRouteKey()])
        ->assertForbidden();
});

it('does not allow form submission with an empty title', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->for($contributor, 'author')->create([
        'title' => 'Original Title',
        'slug' => 'original-title',
        'status' => PageStatus::Draft,
    ]);

    Livewire::actingAs($contributor)
        ->test(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'title' => '',
            'slug' => 'original-title',
            'excerpt' => null,
            'blocks' => [],
        ])
        ->call('save')
        ->assertHasFormErrors(['title' => 'required']);

    expect($page->fresh()->title)->toBe('Original Title');
});

it('does not allow form submission with an invalid slug', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->for($contributor, 'author')->create([
        'title' => 'Original Title',
        'slug' => 'original-title',
        'status' => PageStatus::Draft,
    ]);

    Livewire::actingAs($contributor)
        ->test(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'title' => 'Updated Title',
            'slug' => 'Invalid Slug!',
            'excerpt' => null,
            'blocks' => [],
        ])
        ->call('save')
        ->assertHasErrors();

    expect($page->fresh()->slug)->toBe('original-title');
});

it('preserves the page author and publication fields when editing a draft', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $anotherUser = User::factory()->create();

    $page = Page::factory()->for($contributor, 'author')->create([
        'title' => 'Original Title',
        'slug' => 'original-title',
        'status' => PageStatus::Draft,
        'published_at' => null,
    ]);

    Livewire::actingAs($contributor)
        ->test(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'excerpt' => 'Updated excerpt.',
            'blocks' => [],
            'author_id' => $anotherUser->id,
            'status' => PageStatus::Published->value,
            'published_at' => now()->toDateTimeString(),
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $updatedPage = $page->fresh();

    expect($updatedPage->author_id)->toBe($contributor->id)
        ->and($updatedPage->status)->toBe(PageStatus::Draft)
        ->and($updatedPage->published_at)->toBeNull();
});

it('loads existing structured content blocks into the edit form', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $blocks = [
        [
            'type' => 'hero',
            'data' => [
                'heading' => 'Original Heading',
                'subheading' => 'Original Subheading',
            ],
        ],
        [
            'type' => 'text',
            'data' => [
                'content' => 'Original content.',
            ],
        ],
        [
            'type' => 'gallery',
            'data' => [
                'images' => [],
            ],
        ],
        [
            'type' => 'cta',
            'data' => [
                'heading' => 'Ready to start?',
                'label' => 'Contact us',
                'url' => '/contact',
            ],
        ],
    ];

    $page = Page::factory()->for($contributor, 'author')->create([
        'status' => PageStatus::Draft,
        'blocks' => $blocks,
    ]);

    $component = Livewire::actingAs($contributor)
        ->test(EditPage::class, ['record' => $page->getRouteKey()]);

    $state = $component->get('data.blocks');

    expect(array_values($state))->toBe($blocks);
});

it('saves updates to all supported content block types', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->for($contributor, 'author')->create([
        'status' => PageStatus::Draft,
        'blocks' => [],
    ]);

    $blocks = [
        [
            'type' => 'hero',
            'data' => [
                'heading' => 'Updated Heading',
                'subheading' => 'Updated Subheading',
            ],
        ],
        [
            'type' => 'text',
            'data' => [
                'content' => 'Updated project description.',
            ],
        ],
        [
            'type' => 'gallery',
            'data' => [
                'images' => [],
            ],
        ],
        [
            'type' => 'cta',
            'data' => [
                'heading' => 'Let us work together',
                'label' => 'Get in touch',
                'url' => '/contact',
            ],
        ],
    ];

    Livewire::actingAs($contributor)
        ->test(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'title' => $page->title,
            'slug' => $page->slug,
            'excerpt' => $page->excerpt,
            'blocks' => $blocks,
            'cover_image' => null,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'show_in_navigation' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($page->fresh()->blocks)->toBe($blocks);
});

it('uploads and stores a gallery image when editing a page', function () {
    Storage::fake('public');

    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $page = Page::factory()->for($contributor, 'author')->create([
        'status' => PageStatus::Draft,
        'blocks' => [],
    ]);

    $image = UploadedFile::fake()->image('updated-project.jpg');

    Livewire::actingAs($contributor)
        ->test(EditPage::class, ['record' => $page->getRouteKey()])
        ->fillForm([
            'title' => $page->title,
            'slug' => $page->slug,
            'excerpt' => $page->excerpt,
            'blocks' => [
                [
                    'type' => 'gallery',
                    'data' => [
                        'images' => [
                            [
                                'url' => [$image],
                                'alt' => 'Updated project screenshot',
                            ],
                        ],
                    ],
                ],
            ],
            'cover_image' => null,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'show_in_navigation' => false,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $updatedPage = $page->fresh();
    $galleryImage = $updatedPage->blocks[0]['data']['images'][0];

    expect($galleryImage['alt'])->toBe('Updated project screenshot')
        ->and($galleryImage['url'])->toStartWith('gallery/');

    expect(Storage::disk('public')->exists($galleryImage['url']))->toBeTrue();
});
