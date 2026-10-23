<?php

namespace Database\Seeders;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoPageSeeder extends Seeder
{
    public function run(): void
    {
        $contributor = User::where('email', 'contributor@folio-cms.test')
            ->firstOrFail();

        $secondContributor = User::where('email', 'contributor2@folio-cms.test')
            ->firstOrFail();

        $pages = [
            [
                'title' => 'Creative Portfolio',
                'slug' => 'creative-portfolio',
                'excerpt' => 'A collection of selected creative work, digital experiences, and visual experiments.',
                'author' => $contributor,
                'status' => PageStatus::Published,
                'published_at' => now()->subDays(7),
                'show_in_navigation' => true,
                'meta_title' => 'Creative Portfolio | Folio CMS',
                'meta_description' => 'Explore a collection of creative projects and digital experiences.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Ideas brought to life',
                            'subheading' => 'A selection of thoughtful design and digital work.',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'content' => 'This portfolio showcases the process of turning ideas into useful, engaging digital experiences.',
                        ],
                    ],
                    [
                        'type' => 'gallery',
                        'data' => [
                            'images' => [
                                [
                                    'url' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f',
                                    'alt' => 'Analytics dashboard displayed on a screen',
                                ],
                                [
                                    'url' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085',
                                    'alt' => 'Developer workspace with a laptop',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'cta',
                        'data' => [
                            'heading' => 'Have a project in mind?',
                            'label' => 'Get in touch',
                            'url' => 'mailto:hello@folio-cms.test',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Selected Projects',
                'slug' => 'selected-projects',
                'excerpt' => 'A selection of digital products and design projects.',
                'author' => $contributor,
                'status' => PageStatus::Published,
                'published_at' => now()->subDays(5),
                'show_in_navigation' => true,
                'meta_title' => 'Selected Projects | Folio CMS',
                'meta_description' => 'Discover selected design and development projects.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Selected projects',
                            'subheading' => 'A closer look at recent work.',
                        ],
                    ],
                    [
                        'type' => 'gallery',
                        'data' => [
                            'images' => [
                                [
                                    'url' => 'https://images.unsplash.com/photo-1519389950473-47ba0277781c',
                                    'alt' => 'Team collaborating around a table',
                                ],
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'content' => 'Each project combines research, design, and implementation to solve a specific problem.',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'About the Studio',
                'slug' => 'about-the-studio',
                'excerpt' => 'Learn about the people and principles behind the work.',
                'author' => $secondContributor,
                'status' => PageStatus::Published,
                'published_at' => now()->subDays(3),
                'show_in_navigation' => false,
                'meta_title' => 'About the Studio | Folio CMS',
                'meta_description' => 'Learn about the studio and its approach to creative work.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'A little about us',
                            'subheading' => 'Curiosity, collaboration, and thoughtful execution.',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'content' => 'We bring together creative thinking and practical development to build clear, purposeful digital experiences.',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Brand Refresh Case Study',
                'slug' => 'brand-refresh-case-study',
                'excerpt' => 'A draft case study documenting a brand refresh.',
                'author' => $contributor,
                'status' => PageStatus::Draft,
                'published_at' => null,
                'show_in_navigation' => false,
                'meta_title' => 'Brand Refresh Case Study',
                'meta_description' => 'Draft case study for a brand refresh project.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'A new direction for the brand',
                            'subheading' => 'Draft case study — content in progress.',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'content' => 'This case study will document the research, visual exploration, and final brand system.',
                        ],
                    ],
                ],
            ],
            [
                'title' => 'E-commerce Redesign',
                'slug' => 'e-commerce-redesign',
                'excerpt' => 'A submitted case study awaiting editorial review.',
                'author' => $secondContributor,
                'status' => PageStatus::Pending,
                'published_at' => null,
                'show_in_navigation' => false,
                'meta_title' => 'E-commerce Redesign',
                'meta_description' => 'Case study awaiting editorial review.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'A better shopping experience',
                            'subheading' => 'A redesign focused on clarity and usability.',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'content' => 'The proposal focuses on simplifying navigation and improving the product discovery experience.',
                        ],
                    ],
                    [
                        'type' => 'gallery',
                        'data' => [
                            'images' => [
                                [
                                    'url' => 'https://images.unsplash.com/photo-1556742049-0cfed4f6a45d',
                                    'alt' => 'Customer making a payment at a checkout',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                'title' => 'Upcoming Case Study',
                'slug' => 'upcoming-case-study',
                'excerpt' => 'A completed page scheduled for future publication.',
                'author' => $secondContributor,
                'status' => PageStatus::Published,
                'published_at' => now()->addDays(7),
                'show_in_navigation' => true,
                'meta_title' => 'Upcoming Case Study',
                'meta_description' => 'A case study scheduled for publication.',
                'blocks' => [
                    [
                        'type' => 'hero',
                        'data' => [
                            'heading' => 'Coming soon',
                            'subheading' => 'This project will be available soon.',
                        ],
                    ],
                    [
                        'type' => 'text',
                        'data' => [
                            'content' => 'This page demonstrates scheduled publication and should not appear publicly before its publication date.',
                        ],
                    ],
                ],
            ],
        ];

        foreach ($pages as $data) {
            $page = Page::firstOrNew([
                'slug' => $data['slug'],
            ]);

            $page->title = $data['title'];
            $page->excerpt = $data['excerpt'];
            $page->status = $data['status'];
            $page->published_at = $data['published_at'];
            $page->show_in_navigation = $data['show_in_navigation'];
            $page->meta_title = $data['meta_title'];
            $page->meta_description = $data['meta_description'];
            $page->blocks = $data['blocks'];

            $page->author()->associate($data['author']);
            $page->save();
        }
    }
}
