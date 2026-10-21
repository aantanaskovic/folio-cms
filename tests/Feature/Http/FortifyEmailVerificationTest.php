<?php

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;

it('verifies a user with a valid verification link', function () {
    Event::fake([Verified::class]);

    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $response = $this->actingAs($user)->get($url);

    $response->assertRedirect(route('dashboard'));

    expect($user->fresh()->hasVerifiedEmail())->toBeTrue();

    Event::assertDispatched(Verified::class);
});

it('does not verify a user with an invalid signature', function () {
    $user = User::factory()->unverified()->create();

    $url = route('verification.verify', [
        'id' => $user->id,
        'hash' => sha1($user->getEmailForVerification()),
    ]);

    $response = $this->actingAs($user)->get($url);

    $response->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('does not verify a user when the email hash is incorrect', function () {
    $user = User::factory()->unverified()->create();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1('wrong-email@example.com'),
        ],
    );

    $response = $this->actingAs($user)->get($url);

    $response->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('does not allow a different user to verify the account', function () {
    $user = User::factory()->unverified()->create();
    $differentUser = User::factory()->create();

    $url = URL::temporarySignedRoute(
        'verification.verify',
        now()->addMinutes(60),
        [
            'id' => $user->id,
            'hash' => sha1($user->getEmailForVerification()),
        ],
    );

    $response = $this->actingAs($differentUser)->get($url);

    $response->assertForbidden();

    expect($user->fresh()->hasVerifiedEmail())->toBeFalse();
});

it('sends a verification notification to an authenticated unverified user', function () {
    Notification::fake();

    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)
        ->post(route('verification.send'));

    $response->assertRedirect();

    Notification::assertSentTo($user, VerifyEmail::class);
});

it('does not send a verification notification to an already verified user', function () {
    Notification::fake();

    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('verification.send'));

    $response->assertRedirect();

    Notification::assertNothingSent();
});

it('requires authentication to send a verification notification', function () {
    Notification::fake();

    $response = $this->post(route('verification.send'));

    $response->assertRedirect('/login');

    Notification::assertNothingSent();
});

it('redirects guests to login when visiting the verification notice', function () {
    $this->get(route('verification.notice'))
        ->assertRedirect(route('login'));
});

it('displays the custom email verification page for an unverified user', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertOk()
        ->assertSee('Verify your email address')
        ->assertSee('Resend verification email')
        ->assertSee('name="_token"', false)
        ->assertSee(route('verification.send'), false)
        ->assertSee(route('logout'), false);
});

it('displays a confirmation when a new verification link has been sent', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->withSession(['status' => 'verification-link-sent'])
        ->get(route('verification.notice'))
        ->assertOk()
        ->assertSee('A new verification link has been sent to the email address you provided during registration.');
});
