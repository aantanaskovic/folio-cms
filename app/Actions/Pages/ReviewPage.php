<?php

namespace App\Actions\Pages;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Gate;

class ReviewPage
{
    public function handle(User $user, Page $page, bool $approved): Page
    {
        Gate::forUser($user)->authorize('review', $page);

        if ($page->status !== PageStatus::Pending) {
            throw new AuthorizationException(
                'Only pages pending review can be reviewed.'
            );
        }

        if (! $approved) {
            $page->update([
                'status' => PageStatus::Draft,
                'published_at' => null,
            ]);
        }

        return $page->refresh();
    }
}
