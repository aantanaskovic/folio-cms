<?php

namespace App\Actions\Users;

use App\Models\User;
use App\Validators\Users\UserDataValidator;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreateUser
{
    public function __construct(
        private readonly UserDataValidator $validator,
    ) {}

    public function handle(User $user, array $data): User
    {
        Gate::forUser($user)->authorize('create', User::class);

        $validated = $this->validator->validate($data);

        return DB::transaction(function () use ($validated): User {
            $role = $validated['role'];

            $newUser = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => $validated['password'],
            ]);

            $newUser->assignRole($role);
            $newUser->notify(new VerifyEmail());

            return $newUser;
        });
    }
}
