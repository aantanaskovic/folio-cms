<?php

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

it('sends a password reset link to an existing user', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->from('/forgot-password')->post('/forgot-password', [
        'email' => $user->email,
    ]);

    $response->assertRedirect('/forgot-password')
        ->assertSessionHas('status');

    Notification::assertSentTo($user, ResetPassword::class);
});

it('does not send a password reset notification for an unknown email', function () {
    Notification::fake();

    $response = $this->from('/forgot-password')->post('/forgot-password', [
        'email' => 'unknown@example.com',
    ]);

    $response->assertRedirect('/forgot-password')
        ->assertSessionHasErrors('email');

    Notification::assertNothingSent();
});

it('resets a password with a valid token', function () {
    Event::fake([PasswordReset::class]);

    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $token = Password::createToken($user);
    $newPassword = 'new-secure-password';

    $response = $this->from("/reset-password/{$token}")->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    $response->assertRedirect('/')
        ->assertSessionHas('status');

    expect(Hash::check($newPassword, $user->fresh()->password))->toBeTrue()
        ->and(Hash::check('old-password', $user->fresh()->password))->toBeFalse();

    Event::assertDispatched(PasswordReset::class);
});

it('rejects an invalid password reset token', function () {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $response = $this->from('/reset-password/invalid-token')->post('/reset-password', [
        'token' => 'invalid-token',
        'email' => $user->email,
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertRedirect('/reset-password/invalid-token')
        ->assertSessionHasErrors('email');

    expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
});

it('rejects a password reset when confirmation does not match', function () {
    $user = User::factory()->create();

    $token = Password::createToken($user);

    $response = $this->from("/reset-password/{$token}")->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-secure-password',
        'password_confirmation' => 'different-password',
    ]);

    $response->assertRedirect("/reset-password/{$token}")
        ->assertSessionHasErrors('password');
});

it('rejects a password reset when the password is too short', function () {
    $user = User::factory()->create();

    $token = Password::createToken($user);

    $response = $this->from("/reset-password/{$token}")->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertRedirect("/reset-password/{$token}")
        ->assertSessionHasErrors('password');
});

it('rejects an expired password reset token', function () {
    config(['auth.passwords.users.expire' => 1]);

    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $token = Password::createToken($user);

    $this->travel(2)->minutes();

    $response = $this->from("/reset-password/{$token}")->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => 'new-secure-password',
        'password_confirmation' => 'new-secure-password',
    ]);

    $response->assertRedirect("/reset-password/{$token}")
        ->assertSessionHasErrors('email');

    expect(Hash::check('old-password', $user->fresh()->password))->toBeTrue();
});

it('does not allow a password reset token to be reused', function () {
    $user = User::factory()->create([
        'password' => 'old-password',
    ]);

    $token = Password::createToken($user);

    $firstNewPassword = 'first-new-password';
    $secondNewPassword = 'second-new-password';

    $this->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => $firstNewPassword,
        'password_confirmation' => $firstNewPassword,
    ])->assertRedirect('/');

    expect(Hash::check($firstNewPassword, $user->fresh()->password))->toBeTrue();

    $response = $this->from("/reset-password/{$token}")->post('/reset-password', [
        'token' => $token,
        'email' => $user->email,
        'password' => $secondNewPassword,
        'password_confirmation' => $secondNewPassword,
    ]);

    $response->assertRedirect("/reset-password/{$token}")
        ->assertSessionHasErrors('email');

    expect(Hash::check($firstNewPassword, $user->fresh()->password))->toBeTrue();
});

it('displays the custom forgot password page', function () {
    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee('Forgot your password?')
        ->assertSee('name="email"', false)
        ->assertSee('Send password reset link')
        ->assertSee(route('login'), false);
});

it('displays the custom reset password page with its token', function () {
    $token = 'sample-reset-token';

    $this->get(route('password.reset', [
        'token' => $token,
        'email' => 'jane@example.com',
    ]))
        ->assertOk()
        ->assertSee('Reset your password')
        ->assertSee('name="token" value="' . $token . '"', false)
        ->assertSee('name="email"', false)
        ->assertSee('name="password"', false)
        ->assertSee('name="password_confirmation"', false)
        ->assertSee('jane@example.com');
});
