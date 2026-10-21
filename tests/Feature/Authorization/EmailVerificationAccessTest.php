<?php

use App\Models\User;

it('redirects guests to login when they access the email verification page', function () {
    $this->get(route('verification.notice'))
        ->assertRedirect(route('login'));
});

it('allows unverified users to access the email verification page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('verification.notice'))
        ->assertOk()
        ->assertSee('Verify your email address');
});
