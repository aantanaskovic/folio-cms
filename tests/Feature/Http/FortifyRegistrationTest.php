<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create([
        'name' => 'contributor',
        'guard_name' => 'web',
    ]);

    Role::create([
        'name' => 'admin',
        'guard_name' => 'web',
    ]);
});

it('registers a user through the Fortify endpoint', function () {
    $response = $this->post('/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
    ]);

    $response->assertRedirect('/');

    $user = User::where('email', 'jane@example.com')->firstOrFail();

    expect($user->hasRole('contributor'))->toBeTrue()
        ->and($user->hasRole('admin'))->toBeFalse()
        ->and(Hash::check('StrongPassword123!', $user->password))->toBeTrue();

    $this->assertAuthenticatedAs($user);
});

it('does not register a user with an email that already exists', function () {
    User::factory()->create([
        'email' => 'jane@example.com',
    ]);

    $response = $this->from('/register')->post('/register', [
        'name' => 'Another Jane',
        'email' => 'jane@example.com',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
    ]);

    $response->assertSessionHasErrors('email');

    expect(User::where('email', 'jane@example.com')->count())->toBe(1);
});

it('registers a new user with an unverified email address', function () {
    $response = $this->post('/register', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'StrongPassword123!',
        'password_confirmation' => 'StrongPassword123!',
    ]);

    $response->assertRedirect('/');

    $user = User::where('email', 'jane@example.com')->firstOrFail();

    expect($user->email_verified_at)->toBeNull()
        ->and($user->hasVerifiedEmail())->toBeFalse();
});

it('does not create a user when registration data is invalid', function (
    array $input,
    string $errorField,
) {
    $response = $this->from('/register')->post('/register', $input);

    $response->assertRedirect('/register')
        ->assertSessionHasErrors($errorField);

    expect(User::where('email', 'jane@example.com')->exists())->toBeFalse();
})->with([
    'invalid email' => [
        [
            'name' => 'Jane Doe',
            'email' => 'not-an-email',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
        ],
        'email',
    ],
    'mismatched passwords' => [
        [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'DifferentPassword123!',
        ],
        'password',
    ],
]);

it('displays the custom registration page', function () {
    $this->get(route('register'))
        ->assertOk()
        ->assertSee('Create your account')
        ->assertSee('name="name"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="password_confirmation"', false)
        ->assertSee(route('login'), false);
});
