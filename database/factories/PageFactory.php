<?php

namespace Database\Factories;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'title' => fake()->sentence(3),
            'slug' => fake()->unique()->slug(),
            'excerpt' => fake()->paragraph(),
            'blocks' => [
                [
                    'type' => 'text',
                    'data' => [
                        'content' => fake()->paragraph(),
                    ],
                ],
            ],
            'status' => PageStatus::Draft,
            'published_at' => null,
            'cover_image' => null,
            'meta_title' => null,
            'meta_description' => null,
            'show_in_navigation' => false,
        ];
    }
}
