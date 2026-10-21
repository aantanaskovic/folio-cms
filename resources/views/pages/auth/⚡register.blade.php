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
            Create your account
        </h1>

        <p class="mt-2 text-sm text-gray-600">
            Join Folio CMS and start creating your content.
        </p>

        <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-md sm:p-8">
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="name" class="mb-2 block text-sm font-medium text-gray-700">
                        Full name
                    </label>

                    <input id="name" name="name" type="text" value="{{ old('name') }}" autocomplete="name"
                        required autofocus
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                        placeholder="Jane Doe"
                        @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>

                    @error('name')
                        <p id="name-error" class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-medium text-gray-700">
                        Email address
                    </label>

                    <input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email"
                        required
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
                    <label for="password" class="mb-2 block text-sm font-medium text-gray-700">
                        Password
                    </label>

                    <input id="password" name="password" type="password" autocomplete="new-password" required
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                        placeholder="Create a strong password"
                        @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>

                    @error('password')
                        <p id="password-error" class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-sm font-medium text-gray-700">
                        Confirm password
                    </label>

                    <input id="password_confirmation" name="password_confirmation" type="password"
                        autocomplete="new-password" required
                        class="block w-full rounded-lg border border-gray-300 bg-white px-3 py-2.5 text-sm shadow-sm outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20"
                        placeholder="Repeat your password">
                </div>

                <button type="submit"
                    class="flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Create account
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-gray-600">
            Already have an account?
            <a href="{{ route('login') }}" class="font-semibold text-indigo-600 transition hover:text-indigo-500">
                Sign in
            </a>
        </p>

        <p class="mt-10 text-center text-xs text-gray-500">
            &copy; {{ date('Y') }} Folio CMS
        </p>
    </div>
</div>
