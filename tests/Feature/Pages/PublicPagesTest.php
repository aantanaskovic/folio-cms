<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

it('displays a published page to a guest visitor', function () {
    $author = User::factory()->create();

    $page = Page::factory()
        ->for($author, 'author')
        ->create([
            'title' => 'My Portfolio',
            'slug' => 'my-portfolio',
            'status' => PageStatus::Published,
            'published_at' => now(),
        ]);

    $this->get(route('pages.show', $page->slug))
        ->assertOk()
        ->assertSee('My Portfolio');
});

it('does not expose a draft page to a guest visitor', function () {
    $page = Page::factory()->create([
        'slug' => 'private-draft',
        'status' => PageStatus::Draft,
        'published_at' => null,
    ]);

    $this->get(route('pages.show', $page->slug))
        ->assertNotFound();
});

it('does not expose a page pending review to a guest visitor', function () {
    $page = Page::factory()->create([
        'slug' => 'pending-page',
        'status' => PageStatus::Pending,
        'published_at' => null,
    ]);

    $this->get(route('pages.show', $page->slug))
        ->assertNotFound();
});

it('returns a not found response for an unknown slug', function () {
    $this->get(route('pages.show', 'does-not-exist'))
        ->assertNotFound();
});

it('renders a hero content block', function () {
    $page = Page::factory()->create([
        'slug' => 'hero-example',
        'status' => PageStatus::Published,
        'published_at' => now(),
        'blocks' => [
            [
                'type' => 'hero',
                'data' => [
                    'heading' => 'Build Something Remarkable',
                    'subheading' => 'A portfolio introduction.',
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', $page->slug))
        ->assertOk()
        ->assertSee('Build Something Remarkable')
        ->assertSee('A portfolio introduction.');
});

it('renders a text content block', function () {
    $page = Page::factory()->create([
        'slug' => 'text-example',
        'status' => PageStatus::Published,
        'published_at' => now(),
        'blocks' => [
            [
                'type' => 'text',
                'data' => [
                    'content' => 'This is the portfolio project description.',
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', $page->slug))
        ->assertOk()
        ->assertSee('This is the portfolio project description.');
});

it('renders a gallery content block', function () {
    $page = Page::factory()->create([
        'slug' => 'gallery-example',
        'status' => PageStatus::Published,
        'published_at' => now(),
        'blocks' => [
            [
                'type' => 'gallery',
                'data' => [
                    'images' => [
                        [
                            'url' => '/images/project-one.jpg',
                            'alt' => 'Project dashboard',
                        ],
                        [
                            'url' => '/images/project-two.jpg',
                            'alt' => 'Project analytics',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', $page->slug))
        ->assertOk()
        ->assertSee('Project dashboard')
        ->assertSee('Project analytics')
        ->assertSee('/images/project-one.jpg')
        ->assertSee('/images/project-two.jpg');
});

it('renders a call to action content block', function () {
    $page = Page::factory()->create([
        'slug' => 'cta-example',
        'status' => PageStatus::Published,
        'published_at' => now(),
        'blocks' => [
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Have a project in mind?',
                    'label' => 'Get in touch',
                    'url' => '/contact',
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', $page->slug))
        ->assertOk()
        ->assertSee('Have a project in mind?')
        ->assertSee('Get in touch')
        ->assertSee('href="/contact"', false);
});

it('does not render unknown content block types', function () {
    $page = Page::factory()->create([
        'slug' => 'unknown-block-example',
        'status' => PageStatus::Published,
        'published_at' => now(),
        'blocks' => [
            [
                'type' => 'unknown',
                'data' => [
                    'content' => 'This content should not be displayed.',
                ],
            ],
            [
                'type' => 'text',
                'data' => [
                    'content' => 'This supported content should be displayed.',
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', $page->slug))
        ->assertOk()
        ->assertSee('This supported content should be displayed.')
        ->assertDontSee('This content should not be displayed.');
});


it('renders an uploaded gallery image with its alt text', function () {
    Storage::fake('public');

    $imagePath = 'gallery/project-one.jpg';

    Storage::disk('public')->put($imagePath, 'fake image contents');

    $page = Page::factory()->create([
        'slug' => 'gallery-display-test',
        'status' => PageStatus::Published,
        'published_at' => now(),
        'blocks' => [
            [
                'type' => 'gallery',
                'data' => [
                    'images' => [
                        [
                            'url' => $imagePath,
                            'alt' => 'Project dashboard',
                        ],
                    ],
                ],
            ],
        ],
    ]);

    /** @var FilesystemAdapter $disk */
    $disk = Storage::disk('public');

    $imageUrl = $disk->url($imagePath);

    $this->get(route('pages.show', $page->slug))
        ->assertOk()
        ->assertSee($imageUrl, false)
        ->assertSee('alt="Project dashboard"', false);
});

it('uses the page meta title in the document title when provided', function () {
    Page::factory()->create([
        'title' => 'My Portfolio',
        'slug' => 'my-portfolio',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'meta_title' => 'Custom SEO Title',
    ]);

    $this->get(route('pages.show', 'my-portfolio'))
        ->assertOk()
        ->assertSee('<title>Custom SEO Title — Folio CMS</title>', false);
});

it('renders the page meta description when provided', function () {
    Page::factory()->create([
        'title' => 'My Portfolio',
        'slug' => 'my-portfolio',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'meta_description' => 'A portfolio showcasing selected projects.',
    ]);

    $this->get(route('pages.show', 'my-portfolio'))
        ->assertOk()
        ->assertSee(
            '<meta name="description" content="A portfolio showcasing selected projects.">',
            false,
        );
});

it('falls back to the page title when the meta title is empty', function () {
    Page::factory()->create([
        'title' => 'My Portfolio',
        'slug' => 'my-portfolio',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'meta_title' => null,
    ]);

    $this->get(route('pages.show', 'my-portfolio'))
        ->assertOk()
        ->assertSee('<title>My Portfolio — Folio CMS</title>', false);
});

it('does not expose a published page before its scheduled publication time', function () {
    Page::factory()->create([
        'title' => 'Scheduled Portfolio',
        'slug' => 'scheduled-portfolio',
        'status' => PageStatus::Published,
        'published_at' => now()->addDay(),
    ]);

    $this->get(route('pages.show', 'scheduled-portfolio'))
        ->assertNotFound();
});


it('displays a published page when its publication time arrives', function () {
    $publicationTime = now();

    $this->travelTo($publicationTime);

    Page::factory()->create([
        'title' => 'New Portfolio',
        'slug' => 'new-portfolio',
        'status' => PageStatus::Published,
        'published_at' => $publicationTime,
    ]);

    $this->get(route('pages.show', 'new-portfolio'))
        ->assertOk()
        ->assertSee('New Portfolio');

    $this->travelBack();
});

it('continues to display a published page after its publication time', function () {
    Page::factory()->create([
        'title' => 'Published Portfolio',
        'slug' => 'published-portfolio',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
    ]);

    $this->get(route('pages.show', 'published-portfolio'))
        ->assertOk()
        ->assertSee('Published Portfolio');
});

it('escapes HTML in text content blocks', function () {
    Page::factory()->create([
        'slug' => 'escaped-text',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'blocks' => [
            [
                'type' => 'text',
                'data' => [
                    'content' => '<script>alert("xss")</script>',
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', 'escaped-text'))
        ->assertOk()
        ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert("xss")</script>', false);
});

it('escapes HTML in call to action labels', function () {
    Page::factory()->create([
        'slug' => 'escaped-cta',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'blocks' => [
            [
                'type' => 'cta',
                'data' => [
                    'label' => '<script>alert("xss")</script>',
                    'url' => '/contact',
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', 'escaped-cta'))
        ->assertOk()
        ->assertSee('&lt;script&gt;alert(&quot;xss&quot;)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert("xss")</script>', false);
});

it('renders a relative call to action URL', function () {
    Page::factory()->create([
        'slug' => 'relative-cta',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'blocks' => [
            [
                'type' => 'cta',
                'data' => [
                    'label' => 'Contact us',
                    'url' => '/contact',
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', 'relative-cta'))
        ->assertOk()
        ->assertSee('href="/contact"', false);
});

it('renders an HTTPS call to action URL', function () {
    Page::factory()->create([
        'slug' => 'https-cta',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'blocks' => [
            [
                'type' => 'cta',
                'data' => [
                    'label' => 'Visit our partner',
                    'url' => 'https://example.com/portfolio',
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', 'https-cta'))
        ->assertOk()
        ->assertSee('href="https://example.com/portfolio"', false);
});

it('does not render an active call to action link with a javascript URL', function () {
    Page::factory()->create([
        'slug' => 'javascript-cta',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'blocks' => [
            [
                'type' => 'cta',
                'data' => [
                    'label' => 'Unsafe link',
                    'url' => 'javascript:alert(1)',
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', 'javascript-cta'))
        ->assertOk()
        ->assertDontSee('href="javascript:alert(1)"', false);
});

it('does not render an active call to action link with a data URL', function () {
    Page::factory()->create([
        'slug' => 'data-cta',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'blocks' => [
            [
                'type' => 'cta',
                'data' => [
                    'label' => 'Unsafe link',
                    'url' => 'data:text/html,<script>alert(1)</script>',
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', 'data-cta'))
        ->assertOk()
        ->assertDontSee('href="data:text/html', false);
});

it('does not render a call to action link when its URL is missing', function () {
    Page::factory()->create([
        'slug' => 'missing-cta-url',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'blocks' => [
            [
                'type' => 'cta',
                'data' => [
                    'label' => 'Contact us',
                ],
            ],
        ],
    ]);

    $this->get(route('pages.show', 'missing-cta-url'))
        ->assertOk()
        ->assertDontSee('Contact us');
});
