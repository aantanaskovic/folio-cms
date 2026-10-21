<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.public')] class extends Component {};
?>
<div>
    <section class="relative isolate overflow-hidden bg-white">
        <div class="mx-auto grid max-w-7xl gap-12 px-6 py-20 sm:py-28 lg:grid-cols-2 lg:items-center lg:gap-16 lg:px-8">
            <div class="max-w-2xl">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-indigo-600">
                    Content management, simplified
                </p>

                <h1 class="mt-6 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl">
                    Your content.
                    <span class="text-indigo-600">Your portfolio.</span>
                    One platform.
                </h1>

                <p class="mt-6 max-w-xl text-lg leading-8 text-gray-600">
                    Create, manage, and publish polished pages through a flexible
                    content management system built for modern portfolio websites.
                </p>

                <div class="mt-10 flex flex-col gap-4 sm:flex-row">
                    @guest
                        <a href="{{ route('register') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Get started
                        </a>

                        <a href="{{ route('login') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                            Sign in
                        </a>
                    @endguest

                    @auth
                        @if (auth()->user()->hasVerifiedEmail())
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                                Go to dashboard
                            </a>
                        @else
                            <a href="{{ route('verification.notice') }}"
                                class="inline-flex items-center justify-center rounded-lg bg-indigo-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500">
                                Verify your email
                            </a>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 bg-gray-50 p-6 shadow-sm sm:p-8">
                <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-center justify-between gap-4 border-b border-gray-100 pb-4">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">
                                Content workspace
                            </p>

                            <p class="mt-1 text-sm text-gray-500">
                                A clearer way to manage your pages
                            </p>
                        </div>

                        <span class="rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                            Folio CMS
                        </span>
                    </div>

                    <div class="mt-6 space-y-4">
                        <div class="rounded-lg bg-gray-50 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm font-medium text-gray-900">
                                    About
                                </p>

                                <span class="text-xs font-medium text-gray-500">
                                    Draft
                                </span>
                            </div>

                            <div class="mt-3 h-2 w-3/4 rounded bg-gray-200"></div>
                            <div class="mt-2 h-2 w-1/2 rounded bg-gray-100"></div>
                        </div>

                        <div class="rounded-lg bg-gray-50 p-4">
                            <div class="flex items-center justify-between gap-4">
                                <p class="text-sm font-medium text-gray-900">
                                    Selected work
                                </p>

                                <span class="text-xs font-medium text-emerald-700">
                                    Published
                                </span>
                            </div>

                            <div class="mt-3 h-2 w-2/3 rounded bg-gray-200"></div>
                            <div class="mt-2 h-2 w-1/3 rounded bg-gray-100"></div>
                        </div>

                        <div class="rounded-lg border border-dashed border-gray-300 p-4 text-center">
                            <p class="text-sm font-medium text-gray-600">
                                Your next page starts here
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="border-t border-gray-200 bg-gray-50">
        <div class="mx-auto max-w-7xl px-6 py-16 lg:px-8 lg:py-20">
            <div class="max-w-2xl">
                <h2 class="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">
                    Everything you need to manage your content
                </h2>

                <p class="mt-4 leading-7 text-gray-600">
                    Build structured pages, manage publishing workflows, and keep
                    your portfolio content organized in one place.
                </p>
            </div>

            <div class="mt-10 grid gap-6 md:grid-cols-3">
                <article class="rounded-xl border border-gray-200 bg-white p-6">
                    <h3 class="text-base font-semibold text-gray-900">
                        Flexible page content
                    </h3>

                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Organize pages with reusable content blocks for text,
                        galleries, hero sections, and calls to action.
                    </p>
                </article>

                <article class="rounded-xl border border-gray-200 bg-white p-6">
                    <h3 class="text-base font-semibold text-gray-900">
                        Controlled publishing
                    </h3>

                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Keep drafts separate from published content and use a
                        review workflow before changes go live.
                    </p>
                </article>

                <article class="rounded-xl border border-gray-200 bg-white p-6">
                    <h3 class="text-base font-semibold text-gray-900">
                        Role-based access
                    </h3>

                    <p class="mt-3 text-sm leading-6 text-gray-600">
                        Separate contributor and administrator responsibilities
                        with permission-based access control.
                    </p>
                </article>
            </div>
        </div>
    </section>
</div>
