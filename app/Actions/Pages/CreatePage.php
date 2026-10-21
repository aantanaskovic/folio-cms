<?php

namespace App\Actions\Pages;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use App\Validators\Pages\PageDataValidator;
use Illuminate\Support\Facades\Gate;

class CreatePage
{
    public function __construct(
        private readonly PageDataValidator $validator,
    ) {}

    public function handle(User $user, array $input): Page
    {
        Gate::forUser($user)->authorize('create', Page::class);

        $validated = $this->validator->validate($input);

        return $user->pages()->create([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'] ?? null,
            'blocks' => $validated['blocks'],
            'status' => PageStatus::Draft,
            'published_at' => null,
        ]);
    }
}
