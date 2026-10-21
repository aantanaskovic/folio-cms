<?php

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Notification;

it('logs in a user with valid credentials', function () {
    $user = User::factory()->create([
        'password' => 'correct-password',
    ]);

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'correct-password',
    ]);

    $response->assertRedirect('/');

    $this->assertAuthenticatedAs($user);
});

it('does not log in a user with an incorrect password', function () {
    $user = User::factory()->create([
        'password' => 'correct-password',
    ]);

    $response = $this->from('/login')->post('/login', [
        'email' => $user->email,
        'password' => 'incorrect-password',
    ]);

    $response->assertRedirect('/login')
        ->assertSessionHasErrors();

    $this->assertGuest();
});

it('does not log in a user with an unknown email address', function () {
    $response = $this->from('/login')->post('/login', [
        'email' => 'unknown@example.com',
        'password' => 'correct-password',
    ]);

    $response->assertRedirect('/login')
        ->assertSessionHasErrors();

    $this->assertGuest();
});

it('logs out an authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $response->assertRedirect('/');

    $this->assertGuest();
});

it('regenerates the session after successful login', function () {
    $user = User::factory()->create([
        'password' => 'correct-password',
    ]);

    $sessionId = session()->getId();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'correct-password',
    ]);

    expect(session()->getId())->not->toBe($sessionId);
});

it('displays the custom login page', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('Welcome back')
        ->assertSee('Sign in to manage your content.')
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="remember"', false)
        ->assertSee(route('password.request'), false)
        ->assertSee(route('register'), false);
});
