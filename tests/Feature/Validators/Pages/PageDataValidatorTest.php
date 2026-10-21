<?php

use App\Models\Page;
use App\Validators\Pages\PageDataValidator;
use Illuminate\Validation\ValidationException;

it('validates valid page data', function () {
    $data = [
        'title' => 'My Portfolio',
        'slug' => 'my-portfolio',
        'excerpt' => 'A portfolio page.',
        'blocks' => [
            [
                'type' => 'text',
                'data' => ['content' => 'Welcome to my portfolio.'],
            ],
        ],
    ];

    $validated = app(PageDataValidator::class)->validate($data);

    expect($validated)
        ->toMatchArray($data);
});

it('allows an empty blocks array', function () {
    $data = [
        'title' => 'My Portfolio',
        'slug' => 'my-portfolio',
        'blocks' => [],
    ];

    $validated = app(PageDataValidator::class)->validate($data);

    expect($validated['blocks'])->toBe([]);
});

it('allows a slug to remain unchanged when updating the same page', function () {
    $page = Page::factory()->create([
        'slug' => 'my-portfolio',
    ]);

    $data = [
        'title' => 'Updated Portfolio',
        'slug' => 'my-portfolio',
        'blocks' => [],
    ];

    $validated = app(PageDataValidator::class)->validate($data, $page);

    expect($validated['slug'])->toBe('my-portfolio');
});

it('rejects a slug already used by another page', function () {
    Page::factory()->create([
        'slug' => 'existing-page',
    ]);

    $data = [
        'title' => 'Another Page',
        'slug' => 'existing-page',
        'blocks' => [],
    ];

    expect(fn() => app(PageDataValidator::class)->validate($data))
        ->toThrow(ValidationException::class);
});


it('rejects invalid page data', function (array $data) {
    expect(fn() => app(PageDataValidator::class)->validate($data))
        ->toThrow(ValidationException::class);
})->with([
    'missing title' => [[
        'slug' => 'valid-slug',
        'blocks' => [],
    ]],
    'empty title' => [[
        'title' => '',
        'slug' => 'valid-slug',
        'blocks' => [],
    ]],
    'missing slug' => [[
        'title' => 'Valid Title',
        'blocks' => [],
    ]],
    'empty slug' => [[
        'title' => 'Valid Title',
        'slug' => '',
        'blocks' => [],
    ]],
    'invalid slug format' => [[
        'title' => 'Valid Title',
        'slug' => 'Invalid Slug!',
        'blocks' => [],
    ]],
    'invalid blocks type' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => 'not-an-array',
    ]],
    'unsupported block type' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'video',
                'data' => [],
            ],
        ],
    ]],
    'block missing data' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'text',
            ],
        ],
    ]],
    'block with invalid structure' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            'not-a-block',
        ],
    ]],
    'block with unsupported data type' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'text',
                'data' => 'not-an-array',
            ],
        ],
    ]],
    'text block without content' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'text',
                'data' => [],
            ],
        ],
    ]],
    'hero block without heading' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'hero',
                'data' => [],
            ],
        ],
    ]],
    'hero block with empty heading' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'hero',
                'data' => [
                    'heading' => '',
                ],
            ],
        ],
    ]],
    'hero block with heading longer than 255 characters' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'hero',
                'data' => [
                    'heading' => str_repeat('a', 256),
                ],
            ],
        ],
    ]],
    'text block with empty content' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'text',
                'data' => [
                    'content' => '',
                ],
            ],
        ],
    ]],
    'gallery image without URL' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'gallery',
                'data' => [
                    'images' => [
                        [
                            'alt' => 'Project screenshot',
                        ],
                    ],
                ],
            ],
        ],
    ]],
    'gallery image without alt text' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'gallery',
                'data' => [
                    'images' => [
                        [
                            'url' => 'gallery/project.jpg',
                        ],
                    ],
                ],
            ],
        ],
    ]],
    'cta block without required label' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Contact us',
                    'url' => '/contact',
                ],
            ],
        ],
    ]],
    'cta block without required heading' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'cta',
                'data' => [
                    'label' => 'Contact us',
                    'url' => '/contact',
                ],
            ],
        ],
    ]],
    'cta block without required URL' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Contact us',
                    'label' => 'Get in touch',
                ],
            ],
        ],
    ]],
    'cta block with URL longer than 2048 characters' => [[
        'title' => 'Valid Title',
        'slug' => 'valid-slug',
        'blocks' => [
            [
                'type' => 'cta',
                'data' => [
                    'heading' => 'Discover more',
                    'label' => 'Learn more',
                    'url' => '/' . str_repeat('a', 2048),
                ],
            ],
        ],
    ]],
]);

it('rejects a slug belonging to another page when updating', function () {
    Page::factory()->create([
        'slug' => 'existing-page',
    ]);

    $page = Page::factory()->create([
        'slug' => 'my-page',
    ]);

    expect(fn() => app(PageDataValidator::class)->validate([
        'title' => 'Updated Page',
        'slug' => 'existing-page',
        'blocks' => [],
    ], $page))->toThrow(ValidationException::class);
});
