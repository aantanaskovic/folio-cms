<?php

namespace App\Validators\Users;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserDataValidator
{
    private const ALLOWED_ROLES = ['admin', 'contributor'];

    public function validate(array $data, ?User $user = null): array
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user?->id),
            ],
            'password' => [
                $user ? 'nullable' : 'required',
                'string',
                'min:8',
                'max:255',
            ],
            'role' => ['required', Rule::in(self::ALLOWED_ROLES)],
        ])->validate();
    }
}
