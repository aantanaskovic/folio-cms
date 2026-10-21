<?php

use App\Models\Page;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

it('casts user attributes to their expected types', function () {
    $user = User::factory()->create([
        'email_verified_at' => '2026-10-15 12:00:00',
        'password' => 'plain-text-password',
    ]);

    expect($user->email_verified_at)->toBeInstanceOf(Carbon::class)
        ->and(Hash::check('plain-text-password', $user->password))->toBeTrue()
        ->and($user->password)->not->toBe('plain-text-password');
});

it('hides sensitive attributes from array serialization', function () {
    $user = User::factory()->create();

    expect($user->toArray())
        ->not->toHaveKey('password')
        ->not->toHaveKey('remember_token');
});

it('implements the email verification contract', function () {
    expect(new User)->toBeInstanceOf(MustVerifyEmail::class);
});

it('allows mass assignment only of the intended user attributes', function () {
    $user = new User;

    expect($user->isFillable('name'))->toBeTrue()
        ->and($user->isFillable('email'))->toBeTrue()
        ->and($user->isFillable('password'))->toBeTrue()
        ->and($user->isFillable('remember_token'))->toBeFalse();
});

it('returns only pages belonging to the user', function () {
    $firstAuthor = User::factory()->create();
    $secondAuthor = User::factory()->create();

    $firstPage = $firstAuthor->pages()->create([
        'title' => 'First Page',
        'slug' => 'first-page',
        'blocks' => [],
    ]);

    $secondAuthor->pages()->create([
        'title' => 'Second Page',
        'slug' => 'second-page',
        'blocks' => [],
    ]);

    expect($firstAuthor->pages)->toHaveCount(1)
        ->and($firstAuthor->pages->first()->is($firstPage))->toBeTrue()
        ->and($firstAuthor->pages->contains('slug', 'second-page'))->toBeFalse();
});
