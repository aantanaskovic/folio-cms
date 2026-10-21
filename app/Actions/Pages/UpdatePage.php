<?php

namespace App\Actions\Pages;

use App\Models\Page;
use App\Models\User;
use App\Validators\Pages\PageDataValidator;
use Illuminate\Support\Facades\Gate;

class UpdatePage
{
    public function __construct(
        private readonly PageDataValidator $validator,
    ) {}

    public function handle(User $user, Page $page, array $input): Page
    {
        Gate::forUser($user)->authorize('update', $page);

        $validated = $this->validator->validate($input, $page);

        $page->update([
            'title' => $validated['title'],
            'slug' => $validated['slug'],
            'excerpt' => $validated['excerpt'] ?? null,
            'blocks' => $validated['blocks'],
            'show_in_navigation' => $validated['show_in_navigation']
                ?? $page->show_in_navigation,
        ]);

        return $page->refresh();
    }
}
