<?php

use App\Enums\PageStatus;
use App\Models\Page;
use App\Models\User;

it('displays guest navigation on the public homepage', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('FOLIO CMS')
        ->assertSee(route('login'), false)
        ->assertSee(route('register'), false)
        ->assertDontSee(route('dashboard'), false)
        ->assertDontSee(route('logout'), false);
});

it('displays dashboard and sign out links for a verified user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('dashboard'), false)
        ->assertSee(route('logout'), false)
        ->assertDontSee(route('login'), false)
        ->assertDontSee(route('register'), false);
});

it('displays email verification and sign out links for an unverified user', function () {
    $user = User::factory()->unverified()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertOk()
        ->assertSee(route('verification.notice'), false)
        ->assertSee(route('logout'), false)
        ->assertDontSee(route('dashboard'), false)
        ->assertDontSee(route('login'), false)
        ->assertDontSee(route('register'), false);
});

it('displays published pages marked for public navigation', function () {
    $page = Page::factory()->create([
        'title' => 'My Portfolio',
        'slug' => 'my-portfolio',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'show_in_navigation' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('My Portfolio')
        ->assertSee(route('pages.show', $page->slug), false);
});

it('does not display published pages excluded from public navigation', function () {
    Page::factory()->create([
        'title' => 'Hidden Portfolio',
        'slug' => 'hidden-portfolio',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'show_in_navigation' => false,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Hidden Portfolio');
});

it('does not display unpublished pages in public navigation', function () {
    Page::factory()->create([
        'title' => 'Draft Portfolio',
        'slug' => 'draft-portfolio',
        'status' => PageStatus::Draft,
        'published_at' => null,
        'show_in_navigation' => true,
    ]);

    Page::factory()->create([
        'title' => 'Pending Portfolio',
        'slug' => 'pending-portfolio',
        'status' => PageStatus::Pending,
        'published_at' => null,
        'show_in_navigation' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Draft Portfolio')
        ->assertDontSee('Pending Portfolio');
});

it('does not display a published page before its scheduled publication time', function () {
    Page::factory()->create([
        'title' => 'Scheduled Portfolio',
        'slug' => 'scheduled-portfolio',
        'status' => PageStatus::Published,
        'published_at' => now()->addDay(),
        'show_in_navigation' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Scheduled Portfolio');
});

it('displays a published page in navigation when its publication time arrives', function () {
    Page::factory()->create([
        'title' => 'New Portfolio',
        'slug' => 'new-portfolio',
        'status' => PageStatus::Published,
        'published_at' => now(),
        'show_in_navigation' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertSee('New Portfolio');
});

it('does not display a published page without a publication date', function () {
    Page::factory()->create([
        'title' => 'Undated Portfolio',
        'slug' => 'undated-portfolio',
        'status' => PageStatus::Published,
        'published_at' => null,
        'show_in_navigation' => true,
    ]);

    $this->get(route('home'))
        ->assertOk()
        ->assertDontSee('Undated Portfolio');
});

it('displays published pages in alphabetical order', function () {
    Page::factory()->create([
        'title' => 'Zebra Portfolio',
        'slug' => 'zebra-portfolio',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'show_in_navigation' => true,
    ]);

    Page::factory()->create([
        'title' => 'Alpha Portfolio',
        'slug' => 'alpha-portfolio',
        'status' => PageStatus::Published,
        'published_at' => now()->subDay(),
        'show_in_navigation' => true,
    ]);

    $response = $this->get(route('home'))->assertOk();

    $response->assertSeeInOrder([
        'Alpha Portfolio',
        'Zebra Portfolio',
    ]);
});
