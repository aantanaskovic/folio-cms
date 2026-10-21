<?php

use App\Models\User;

it('displays the public landing page content', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Your content.')
        ->assertSee('Your portfolio.')
        ->assertSee('Content management, simplified')
        ->assertSee('Flexible page content')
        ->assertSee('Controlled publishing')
        ->assertSee('Role-based access')
        ->assertSee('All rights reserved.')
        ->assertSee(date('Y'));
});

it('shows registration and login calls to action to guests', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee(route('register'), false)
        ->assertSee(route('login'), false)
        ->assertSee('Get started')
        ->assertSee('Sign in');
});

it('shows the dashboard call to action to verified users', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('dashboard'), false)
        ->assertSee('Go to dashboard')
        ->assertDontSee('Get started');
});

it('prompts unverified users to verify their email', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('verification.notice'), false)
        ->assertSee('Verify your email')
        ->assertDontSee('Go to dashboard');
});
