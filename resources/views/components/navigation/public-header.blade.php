<?php

use App\Actions\Pages\GetPublicNavigationPages;

$navigationPages = app(GetPublicNavigationPages::class)->handle();

?>
<header class="border-b border-gray-200 bg-white">
    <nav class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-6 py-4 lg:px-8" aria-label="Main navigation">
        <a href="{{ route('home') }}" class="text-lg font-bold tracking-tight text-gray-900">
            FOLIO CMS
        </a>

        @if ($navigationPages->isNotEmpty())
            <div class="group relative">
                <button type="button"
                    class="flex items-center gap-2 text-sm font-medium text-gray-600 transition hover:text-gray-900 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-indigo-600"
                    aria-haspopup="true">
                    Pages

                    <svg class="h-4 w-4 transition-transform group-hover:rotate-180 group-focus-within:rotate-180"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M5.22 7.47a.75.75 0 0 1 1.06 0L10 11.19l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.53a.75.75 0 0 1 0-1.06Z"
                            clip-rule="evenodd" />
                    </svg>
                </button>

                <div
                    class="absolute left-0 top-full z-50 hidden min-w-56 pt-3 group-hover:block group-focus-within:block">
                    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white py-2 shadow-lg">
                        @foreach ($navigationPages as $page)
                            <a href="{{ route('pages.show', $page->slug) }}"
                                class="block whitespace-nowrap px-4 py-2.5 text-sm font-medium text-gray-700 transition hover:bg-gray-50 hover:text-indigo-600">
                                {{ $page->title }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

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
