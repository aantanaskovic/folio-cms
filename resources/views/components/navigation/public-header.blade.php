<?php

use App\Actions\Pages\GetPublicNavigationPages;

$navigationPages = app(GetPublicNavigationPages::class)->handle();

?>
<header class="border-b border-gray-200 bg-white">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-4 lg:px-8" aria-label="Main navigation">
        <a href="{{ route('home') }}" class="text-lg font-bold tracking-tight text-gray-900">
            FOLIO CMS
        </a>

        <div class="flex items-center gap-4">
            @foreach ($navigationPages as $page)
                <a href="{{ route('pages.show', $page->slug) }}"
                    class="text-sm font-medium text-gray-600 transition hover:text-gray-900">
                    {{ $page->title }}
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-4 sm:gap-6">
            @guest
                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 transition hover:text-gray-900">
                    Sign in
                </a>

                <a href="{{ route('register') }}"
                    class="rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-indigo-500">
                    Get started
                </a>
            @endguest

            @auth
                @if (auth()->user()->hasVerifiedEmail())
                    <a href="{{ route('dashboard') }}"
                        class="text-sm font-medium text-gray-600 transition hover:text-gray-900">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('verification.notice') }}"
                        class="text-sm font-medium text-gray-600 transition hover:text-gray-900">
                        Verify email
                    </a>
                @endif

                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button type="submit" class="text-sm font-medium text-gray-600 transition hover:text-gray-900">
                        Sign out
                    </button>
                </form>
            @endauth
        </div>
    </nav>
</header>
