<?php

namespace App\Actions\Pages;

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class SubmitPageForReview
{
    public function handle(User $user, Page $page): Page
    {
        Gate::forUser($user)->authorize('submitForReview', $page);

        $page->update([
            'status' => PageStatus::Pending,
        ]);

        return $page->refresh();
    }
}
