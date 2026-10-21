<header class="border-b border-gray-200 bg-white">
    <nav class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-6 py-4 lg:px-8"
        aria-label="Dashboard navigation">
        <a href="{{ route('dashboard') }}" class="text-lg font-bold tracking-tight text-gray-900">
            FOLIO CMS
        </a>

        <div class="flex flex-wrap items-center gap-4 sm:gap-6">
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-gray-700 transition hover:text-indigo-600">
                Dashboard
            </a>

            @can('pages.view')
                <a href="{{ route('filament.admin.resources.pages.index') }}"
                    class="text-sm font-medium text-gray-700 transition hover:text-indigo-600">
                    Pages
                </a>
            @endcan

            @can('users.view')
                <a href="{{ filament()->getPanel('admin')->getUrl() }}"
                    class="text-sm font-medium text-gray-700 transition hover:text-indigo-600">
                    Administration
                </a>
            @endcan

            <a href="{{ route('home') }}" class="text-sm font-medium text-gray-600 transition hover:text-gray-900">
                View website
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="text-sm font-medium text-gray-600 transition hover:text-gray-900">
                    Sign out
                </button>
            </form>
        </div>
    </nav>
</header>
