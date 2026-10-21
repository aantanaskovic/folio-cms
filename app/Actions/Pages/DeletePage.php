<?php

namespace App\Actions\Pages;

use App\Models\Page;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class DeletePage
{
    public function handle(User $user, Page $page): void
    {
        Gate::forUser($user)->authorize('delete', $page);

        $page->delete();
    }
}
