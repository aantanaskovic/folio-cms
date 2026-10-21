<?php

use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.app')] class extends Component {};
?>

<div>
    <div>
        <p class="text-sm font-semibold text-indigo-600">
            FOLIO CMS WORKSPACE
        </p>

        <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">
            Dashboard
        </h1>

        <p class="mt-3 text-gray-600">
            Welcome back, {{ auth()->user()->name }}.
            Manage your content and keep your portfolio organized.
        </p>
    </div>

    <section class="mt-10" aria-labelledby="workspace-heading">
        <h2 id="workspace-heading" class="text-lg font-semibold text-gray-900">
            Your workspace
        </h2>

        <div class="mt-5 grid gap-6 md:grid-cols-2">
            @can('pages.view')
                <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-gray-900">
                        Pages
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        Create, edit, and manage portfolio pages.
                    </p>

                    <div class="mt-4 flex flex-wrap gap-4 text-sm font-semibold">
                        <a href="{{ route('filament.admin.resources.pages.index') }}"
                            class="text-indigo-600 transition hover:text-indigo-500">
                            Manage pages
                        </a>

                        @can('pages.create')
                            <a href="{{ route('filament.admin.resources.pages.create') }}"
                                class="text-indigo-600 transition hover:text-indigo-500">
                                Create page
                            </a>
                        @endcan
                    </div>
                </article>
            @endcan

            @role('admin')
                <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <h3 class="font-semibold text-gray-900">
                        Administration
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-gray-600">
                        Manage access and oversee the content publishing
                        workflow.
                    </p>

                    <a href="{{ filament()->getPanel('admin')->getUrl() }}"
                        class="mt-4 inline-flex text-sm font-semibold text-indigo-600 transition hover:text-indigo-500">
                        Open admin panel
                    </a>
                </article>
            @endrole
        </div>
    </section>
</div>
