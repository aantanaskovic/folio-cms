<?php

namespace App\Policies;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('pages.view');
    }

    public function view(User $user, Page $page): bool
    {
        if ($page->status === PageStatus::Published) {
            return true;
        }

        return $user->can('pages.view')
            && (
                $user->can('pages.review')
                || $page->author_id === $user->id
            );
    }

    public function create(User $user): bool
    {
        return $user->can('pages.create');
    }

    public function update(User $user, Page $page): bool
    {
        if (! $user->can('pages.update')) {
            return false;
        }

        if ($user->can('pages.review')) {
            return true;
        }

        return $page->author_id === $user->id
            && $page->status !== PageStatus::Published;
    }

    public function delete(User $user, Page $page): bool
    {
        if ($user->can('pages.review') && $user->can('pages.delete')) {
            return true;
        }

        return $user->can('pages.create')
            && $page->author_id === $user->id
            && $page->status === PageStatus::Draft;
    }

    public function submitForReview(User $user, Page $page): bool
    {
        return $user->can('pages.submit-for-review')
            && $page->author_id === $user->id
            && $page->status === PageStatus::Draft;
    }

    public function review(User $user, Page $page): bool
    {
        return $user->can('pages.review');
    }

    public function publish(User $user, Page $page): bool
    {
        return $user->can('pages.publish');
    }
}
