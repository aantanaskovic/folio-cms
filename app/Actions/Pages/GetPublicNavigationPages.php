<?php

namespace App\Actions\Pages;

use App\Enums\PageStatus;
use App\Models\Page;
use Illuminate\Database\Eloquent\Collection;

class GetPublicNavigationPages
{
    /**
     * @return Collection<int, Page>
     */
    public function handle(): Collection
    {
        return Page::query()
            ->where('status', PageStatus::Published)
            ->where('show_in_navigation', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->orderBy('title')
            ->get();
    }
}
