<?php

use App\Enums\PageStatus;
use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('components.layouts.public')] class extends Component {
    public Page $page;

    public function mount(string $slug): void
    {
        $this->page = Page::query()->where('slug', $slug)->where('status', PageStatus::Published)->whereNotNull('published_at')->where('published_at', '<=', now())->firstOrFail();
    }

    public function rendering($view, $data): void
    {
        $view->layoutData([
            'title' => $this->page->meta_title ?: $this->page->title,
            'metaDescription' => $this->page->meta_description,
        ]);
    }
};
?>

<main class="mx-auto max-w-5xl px-6 py-16">
    <article>
        <header>
            <h1 class="text-4xl font-bold tracking-tight text-gray-900">
                {{ $page->title }}
            </h1>

            @if ($page->excerpt)
                <p class="mt-6 text-lg leading-8 text-gray-600">
                    {{ $page->excerpt }}
                </p>
            @endif
        </header>

        @foreach ($page->blocks ?? [] as $block)
            @php
                $type = $block['type'] ?? null;
                $data = $block['data'] ?? [];
            @endphp

            @switch($type)
                @case('hero')
                    <section class="mt-12">
                        @if (!empty($data['heading']))
                            <h2 class="text-3xl font-bold tracking-tight text-gray-900">
                                {{ $data['heading'] }}
                            </h2>
                        @endif

                        @if (!empty($data['subheading']))
                            <p class="mt-4 text-lg leading-8 text-gray-600">
                                {{ $data['subheading'] }}
                            </p>
                        @endif
                    </section>
                @break

                @case('text')
                    @if (!empty($data['content']))
                        <section class="mt-10">
                            <div class="leading-8 text-gray-700">
                                {{ $data['content'] }}
                            </div>
                        </section>
                    @endif
                @break

                @case('gallery')
                    @if (!empty($data['images']) && is_array($data['images']))
                        <section class="mt-10">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                @foreach ($data['images'] as $image)
                                    @php
                                        $url = is_array($image)
                                            ? $image['url'] ?? null
                                            : (is_string($image)
                                                ? $image
                                                : null);

                                        $alt = is_array($image) ? $image['alt'] ?? '' : '';

                                        if (
                                            $url &&
                                            !str_starts_with($url, '/') &&
                                            !filter_var($url, FILTER_VALIDATE_URL)
                                        ) {
                                            $url = \Illuminate\Support\Facades\Storage::disk('public')->url($url);
                                        }
                                    @endphp

                                    @if ($url)
                                        <figure>
                                            <img src="{{ $url }}" alt="{{ $alt }}"
                                                class="h-auto w-full rounded-xl object-cover">

                                            @if ($alt)
                                                <figcaption class="mt-2 text-sm text-gray-500">
                                                    {{ $alt }}
                                                </figcaption>
                                            @endif
                                        </figure>
                                    @endif
                                @endforeach
                            </div>
                        </section>
                    @endif
                @break

                @case('cta')
                    @php
                        $url = $data['url'] ?? null;

                        $isSafeUrl =
                            is_string($url) &&
                            (str_starts_with($url, '/') ||
                                str_starts_with($url, '#') ||
                                str_starts_with($url, '?') ||
                                preg_match('/^https:\/\//i', $url));
                    @endphp
                    <section class="mt-12 rounded-2xl bg-gray-100 p-8">
                        @if (!empty($data['heading']))
                            <h2 class="text-2xl font-bold tracking-tight text-gray-900">
                                {{ $data['heading'] }}
                            </h2>
                        @endif

                        @if (!empty($data['label']) && $isSafeUrl)
                            <a href="{{ $data['url'] }}"
                                class="mt-6 inline-flex items-center rounded-lg bg-gray-900 px-5 py-3 text-sm font-semibold text-white transition hover:bg-gray-700">
                                {{ $data['label'] }}
                            </a>
                        @endif
                    </section>
                @break
            @endswitch
        @endforeach
    </article>
</main>
