<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.auth')] class extends Component {};
?>

<div class="flex min-h-screen flex-col justify-center px-6 py-12 sm:px-8">
    <div class="mx-auto w-full max-w-md">
        <a href="{{ route('home') }}" class="text-sm font-semibold tracking-wide text-indigo-600">
            FOLIO CMS
        </a>

        <h1 class="mt-8 text-3xl font-semibold tracking-tight text-gray-900">
            Welcome back
        </h1>

        <p class="mt-2 text-sm text-gray-600">
            Sign in to manage your content.
        </p>

        <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-md sm:p-8">
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700">
                        Email address
                    </label>

                    <input id="email" name="email" type="email" value="{{ old('email') }}"
                        autocomplete="username" required autofocus
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                        placeholder="you@example.com"
                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>

                    @error('email')
                        <p id="email-error" class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <div class="mb-2 flex items-center justify-between gap-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Password
                        </label>

                        <a href="{{ route('password.request') }}"
                            class="text-sm font-medium text-indigo-600 transition hover:text-indigo-500">
                            Forgot password?
                        </a>
                    </div>

                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                        placeholder="Enter your password">
                </div>

                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox" value="1"
                        class="size-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">

                    <label for="remember" class="ml-2 block text-sm text-gray-600">
                        Remember me
                    </label>
                </div>

                <button type="submit"
                    class="flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Sign in
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-gray-600">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-semibold text-indigo-600 transition hover:text-indigo-500">
                Create an account
            </a>
        </p>

        <p class="mt-10 text-center text-xs text-gray-500">
            &copy; {{ date('Y') }} Folio CMS
        </p>
    </div>
</div>
