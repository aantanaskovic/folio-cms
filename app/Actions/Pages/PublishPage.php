<?php

namespace App\Actions\Pages;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;


class PublishPage
{
    public function handle(User $user, Page $page): Page
    {
        Gate::forUser($user)->authorize('publish', $page);

        if ($page->status !== PageStatus::Pending) {
            throw new AuthorizationException(
                'Only pages pending review can be published.'
            );
        }

        $page->update([
            'status' => PageStatus::Published,
            'published_at' => now(),
        ]);

        return $page->refresh();
    }
}
