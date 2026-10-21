<?php

use App\Enums\PageStatus;
use App\Filament\Resources\Pages\Pages\CreatePage;
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

function validPageFormData(array $overrides = []): array
{
    return array_merge([
        'title' => 'My Portfolio Project',
        'slug' => 'my-portfolio-project',
        'excerpt' => 'A short project description.',
        'blocks' => [],
        'cover_image' => null,
        'meta_title' => 'My Portfolio Project',
        'meta_description' => 'A portfolio project description.',
        'show_in_navigation' => false,
    ], $overrides);
}

it('allows a contributor to create a page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    Livewire::actingAs($contributor)
        ->test(CreatePage::class)
        ->fillForm(validPageFormData())
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas('pages', [
        'title' => 'My Portfolio Project',
        'slug' => 'my-portfolio-project',
        'author_id' => $contributor->id,
    ]);
});

it('assigns the authenticated contributor as the page author', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $otherUser = User::factory()->create();

    Livewire::actingAs($contributor)
        ->test(CreatePage::class)
        ->fillForm(validPageFormData([
            'author_id' => $otherUser->id,
        ]))
        ->call('create')
        ->assertHasNoFormErrors();

    $page = Page::query()
        ->where('slug', 'my-portfolio-project')
        ->firstOrFail();

    expect($page->author_id)->toBe($contributor->id);
});

it('creates a page as a draft without a publication date', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    Livewire::actingAs($contributor)
        ->test(CreatePage::class)
        ->fillForm(validPageFormData())
        ->call('create')
        ->assertHasNoFormErrors();

    $page = Page::query()
        ->where('slug', 'my-portfolio-project')
        ->firstOrFail();

    expect($page->status)->toBe(PageStatus::Draft)
        ->and($page->published_at)->toBeNull();
});

it('requires a title when creating a page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    Livewire::actingAs($contributor)
        ->test(CreatePage::class)
        ->fillForm(validPageFormData([
            'title' => '',
        ]))
        ->call('create')
        ->assertHasFormErrors([
            'title' => 'required',
        ]);

    $this->assertDatabaseMissing('pages', [
        'slug' => 'my-portfolio-project',
    ]);
});

it('requires a slug when creating a page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    Livewire::actingAs($contributor)
        ->test(CreatePage::class)
        ->fillForm(validPageFormData([
            'slug' => '',
        ]))
        ->call('create')
        ->assertHasFormErrors([
            'slug' => 'required',
        ]);

    $this->assertDatabaseMissing('pages', [
        'title' => 'My Portfolio Project',
    ]);
});

it('validates that the slug is unique', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    Page::factory()->create([
        'slug' => 'my-portfolio-project',
    ]);

    Livewire::actingAs($contributor)
        ->test(CreatePage::class)
        ->fillForm(validPageFormData([
            'slug' => 'my-portfolio-project',
        ]))
        ->call('create')
        ->assertHasFormErrors(['slug' => 'unique']);
});

it('does not allow a user without page creation permission to access the create page', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    Role::findByName('contributor')->revokePermissionTo('pages.create');

    Livewire::actingAs($contributor)
        ->test(CreatePage::class)
        ->assertForbidden();
});


it('creates a page with structured content blocks', function () {
    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $blocks = [
        [
            'type' => 'hero',
            'data' => [
                'heading' => 'Build Something Remarkable',
                'subheading' => 'A portfolio introduction.',
            ],
        ],
        [
            'type' => 'text',
            'data' => [
                'content' => 'Project description.',
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
                'heading' => 'Have a project in mind?',
                'label' => 'Get in touch',
                'url' => '/contact',
            ],
        ],
    ];

    Livewire::actingAs($contributor)
        ->test(CreatePage::class)
        ->fillForm(validPageFormData([
            'blocks' => $blocks,
        ]))
        ->call('create')
        ->assertHasNoFormErrors();

    $page = Page::query()
        ->where('slug', 'my-portfolio-project')
        ->firstOrFail();

    expect($page->blocks)->toBe($blocks);
});


it('uploads and stores a gallery image when creating a page', function () {
    Storage::fake('public');

    $contributor = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $contributor->assignRole('contributor');

    $image = UploadedFile::fake()->image('project-one.jpg');

    Livewire::actingAs($contributor)
        ->test(CreatePage::class)
        ->fillForm(validPageFormData([
            'title' => 'Gallery Project',
            'slug' => 'gallery-project',
            'blocks' => [
                [
                    'type' => 'gallery',
                    'data' => [
                        'images' => [
                            [
                                'url' => [$image],
                                'alt' => 'Project dashboard',
                            ],
                        ],
                    ],
                ],
            ],
        ]))
        ->call('create')
        ->assertHasNoFormErrors();

    $page = Page::query()
        ->where('slug', 'gallery-project')
        ->firstOrFail();

    $galleryImage = $page->blocks[0]['data']['images'][0];

    expect($galleryImage['alt'])->toBe('Project dashboard')
        ->and($galleryImage['url'])->toStartWith('gallery/');

    expect(Storage::disk('public')->exists($galleryImage['url']))->toBeTrue();
});
