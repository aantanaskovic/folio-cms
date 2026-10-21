<?php

use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;

it('redirects guests to login when they access the dashboard', function () {
    $this->get(route('dashboard'))
        ->assertRedirect(route('login'));
});

it('redirects unverified users away from the dashboard', function () {
    $user = User::factory()->create([
        'email_verified_at' => null,
    ]);

    expect($user->hasVerifiedEmail())->toBeFalse()
        ->and($user instanceof MustVerifyEmail)->toBeTrue();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect(route('verification.notice'));
});

it('allows verified users to access the dashboard', function () {
    $user = User::factory()->create([
        'email_verified_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dashboard');
});
