@props([
    'action' => route('search.index'),
    'id' => 'global-search',
    'placeholder' => 'جستجوی در پارتس‌مال...',
    'variant' => 'compact',
])

@php
    $isHero = $variant === 'hero';
@endphp

<form method="GET" action="{{ $action }}" {{ $attributes }}>
    <label for="{{ $id }}" class="sr-only">جستجوی سایت</label>

    <div @class([
        'flex gap-2',
        'rounded-full border border-line bg-white p-1.5 shadow-sm transition focus-within:border-brand/40 focus-within:ring-2 focus-within:ring-brand/20' => $isHero,
    ])>
        <div class="relative min-w-0 flex-1">
            <svg @class([
                'pointer-events-none absolute top-1/2 -translate-y-1/2 text-ink-muted',
                'inset-s-4 size-5' => $isHero,
                'inset-s-3 size-4' => ! $isHero,
            ]) fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
                id="{{ $id }}"
                type="search"
                name="q"
                placeholder="{{ $placeholder }}"
                autocomplete="off"
                @class([
                    'w-full bg-white text-sm text-ink outline-none transition placeholder:text-ink-muted',
                    'h-11 rounded-full border-0 pe-3 ps-12 focus:ring-0' => $isHero,
                    'h-10 rounded-xl border border-line pe-3 ps-10 focus:border-brand/40 focus:ring-2 focus:ring-brand/20' => ! $isHero,
                ])
            >
        </div>

        <button type="submit" @class([
            'inline-flex shrink-0 items-center justify-center bg-brand text-sm font-semibold text-white transition hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand/20',
            'h-11 rounded-full px-5' => $isHero,
            'h-10 rounded-xl px-4' => ! $isHero,
        ])>
            جستجو
        </button>
    </div>
</form>
