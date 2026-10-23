<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DeleteUser
{
    public function handle(User $user, User $targetUser): void
    {
        Gate::forUser($user)->authorize('delete', $targetUser);

        DB::transaction(function () use ($targetUser): void {
            $targetUser->delete();
        });
    }
}
