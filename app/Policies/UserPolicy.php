<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('users.view');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('users.view');
    }

    public function create(User $user): bool
    {
        return $user->can('users.manage');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('users.manage');
    }

    public function delete(User $user, User $model): bool
    {
        if (! $user->can('users.manage') || $user->is($model)) {
            return false;
        }

        if ($model->hasRole('admin')) {
            return User::query()
                ->whereHas('roles', fn($query) => $query->where('name', 'admin'))
                ->count() > 1;
        }

        return true;
    }
}
