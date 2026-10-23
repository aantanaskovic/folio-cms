<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DemoUserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Folio Admin',
                'email' => 'admin@folio-cms.test',
                'role' => 'admin',
            ],
            [
                'name' => 'Alex Morgan',
                'email' => 'contributor@folio-cms.test',
                'role' => 'contributor',
            ],
            [
                'name' => 'Taylor Brooks',
                'email' => 'contributor2@folio-cms.test',
                'role' => 'contributor',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrNew([
                'email' => $data['email'],
            ]);

            $user->name = $data['name'];
            $user->password = 'password';
            $user->email_verified_at = now();
            $user->save();

            $user->syncRoles([$data['role']]);
        }
    }
}
