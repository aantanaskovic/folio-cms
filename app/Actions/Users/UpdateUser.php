<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Validators\Users\UserDataValidator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class UpdateUser
{
    public function __construct(
        private readonly UserDataValidator $validator,
    ) {}

    public function handle(User $user, User $targetUser, array $data): User
    {
        Gate::forUser($user)->authorize('update', $targetUser);

        $validated = $this->validator->validate($data, $targetUser);

        $role = $validated['role'];

        if ($targetUser->is($user) && $role !== 'admin') {
            throw ValidationException::withMessages([
                'role' => 'You cannot change your own administrator role.',
            ]);
        }

        if (
            $targetUser->hasRole('admin')
            && $role !== 'admin'
            && User::role('admin')->count() <= 1
        ) {
            throw ValidationException::withMessages([
                'role' => 'The last administrator cannot be demoted.',
            ]);
        }

        return DB::transaction(function () use ($targetUser, $validated, $role): User {
            $targetUser->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if (filled($validated['password'] ?? null)) {
                $targetUser->password = $validated['password'];
            }

            $targetUser->save();
            $targetUser->syncRoles([$role]);

            return $targetUser;
        });
    }
}
