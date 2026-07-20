@props([
    'categories' => null,
])

@php
    $categories = $categories ?? config('partsmall.home_part_categories', []);
    $accents = [
        'from-[#1a2332] via-[#243044] to-[#2d3a4f]',
        'from-[#1f2838] via-[#2a3448] to-[#334056]',
        'from-[#182030] via-[#222c3e] to-[#2b364a]',
    ];

    $partNames = collect($categories)
        ->flatMap(fn (array $category) => $category['parts'] ?? [])
        ->filter()
        ->unique()
        ->values();

    $partsByName = $partNames->isEmpty()
        ? collect()
        : \App\Models\Part::query()
            ->whereIn('name', $partNames->all())
            ->get(['name', 'slug'])
            ->keyBy('name');
@endphp

@if ($categories !== [])
    <section
        {{ $attributes->merge(['class' => 'mb-12']) }}
        aria-labelledby="home-part-categories-title"
        data-part-category-cards
    >
        <div class="mb-7 text-center sm:mb-8 sm:text-start">
            <p class="ps-section-label">دسته‌بندی قطعات</p>
            <h2 id="home-part-categories-title" class="ps-section-title mt-1">
                مسیر سریع به قطعات پرکاربرد
            </h2>
            <p class="mt-2 text-sm text-ink-muted">
                <span class="hidden sm:inline">روی هر دسته نگه دارید تا نمونه‌قطعات را ببینید</span>
                <span class="sm:hidden">روی هر دسته بزنید تا نمونه‌قطعات را ببینید</span>
            </p>
        </div>

        <div class="ps-part-cats grid grid-cols-1 gap-5 sm:grid-cols-3 sm:gap-4 lg:gap-6">
            @foreach ($categories as $index => $category)
                <article
                    class="ps-part-cat"
                    data-part-category-card
                    style="--part-cat-delay: {{ $index * 80 }}ms"
                >
                    <div class="ps-part-cat__shell bg-gradient-to-br {{ $accents[$index % count($accents)] }}">
                        <div class="ps-part-cat__face">
                            <div class="ps-part-cat__panel ps-part-cat__panel--category">
                                <span class="ps-part-cat__index" aria-hidden="true">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                <h3 class="ps-part-cat__title">{{ $category['title'] ?? '' }}</h3>
                                <p class="ps-part-cat__hint">مشاهده قطعات</p>
                            </div>
                            <div class="ps-part-cat__panel ps-part-cat__panel--parts" aria-hidden="true">
                                <p class="ps-part-cat__back-label">{{ $category['title'] ?? '' }}</p>
                                <ul class="ps-part-cat__parts">
                                    @foreach (($category['parts'] ?? []) as $partName)
                                        @php
                                            $part = $partsByName->get($partName);
                                        @endphp
                                        <li>
                                            @if ($part)
                                                <a
                                                    href="{{ route('part.show', $part->slug) }}"
                                                    class="ps-part-cat__part-link"
                                                >{{ $partName }}</a>
                                            @else
                                                <span>{{ $partName }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endif
