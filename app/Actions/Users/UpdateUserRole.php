<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UpdateUserRole
{
    public function handle(User $user, string $roleName): void
    {
        if (! in_array($roleName, ['admin', 'contributor'], true)) {
            throw ValidationException::withMessages([
                'role' => 'The selected role is invalid.',
            ]);
        }

        DB::transaction(function () use ($user, $roleName): void {
            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($user->getKey());

            if (
                $user->hasRole('admin')
                && $roleName !== 'admin'
                && User::role('admin')->count() <= 1
            ) {
                throw ValidationException::withMessages([
                    'role' => 'The last administrator cannot be demoted.',
                ]);
            }

            $role = Role::findByName($roleName, 'web');

            $user->syncRoles([$role]);
        });
    }
}
