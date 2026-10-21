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

        <div class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 shadow-md sm:p-8">
            <div class="flex size-12 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="size-6" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21.75 7.5v9A2.25 2.25 0 0 1 19.5 18.75h-15A2.25 2.25 0 0 1 2.25 16.5v-9M3 6.75l8.1 5.4a1.65 1.65 0 0 0 1.8 0L21 6.75" />
                </svg>
            </div>

            <h1 class="mt-6 text-2xl font-semibold tracking-tight text-gray-900">
                Verify your email address
            </h1>

            <p class="mt-3 text-sm leading-6 text-gray-600">
                Before you can continue, please verify your email address by clicking the link we sent you.
                If you haven't received the email, you can request another one below.
            </p>

            @if (session('status') === 'verification-link-sent')
                <div role="status"
                    class="mt-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
                    A new verification link has been sent to the email address you provided during registration.
                </div>
            @endif

            <form method="POST" action="{{ route('verification.send') }}" class="mt-6">
                @csrf

                <button type="submit"
                    class="flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Resend verification email
                </button>
            </form>

            <div class="my-6 border-t border-gray-200"></div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit"
                    class="flex w-full justify-center rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Sign out
                </button>
            </form>
        </div>

        <p class="mt-10 text-center text-xs text-gray-500">
            &copy; {{ date('Y') }} Folio CMS
        </p>
    </div>
</div>
