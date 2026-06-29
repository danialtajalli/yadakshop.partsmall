@props([
    'action' => route('search.index'),
    'id' => 'global-search',
    'placeholder' => 'جستجوی در پارتس‌مال...',
])

<form method="GET" action="{{ $action }}" {{ $attributes }}>
    <label for="{{ $id }}" class="sr-only">جستجوی سایت</label>

    <div class="flex gap-2">
        <div class="relative min-w-0 flex-1">
            <svg class="pointer-events-none absolute inset-s-3 top-1/2 size-4 -translate-y-1/2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
                id="{{ $id }}"
                type="search"
                name="q"
                placeholder="{{ $placeholder }}"
                autocomplete="off"
                class="h-10 w-full rounded-xl border border-line bg-white pe-3 ps-10 text-sm text-ink outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
            >
        </div>

        <button type="submit" class="inline-flex h-10 shrink-0 items-center justify-center rounded-xl bg-brand px-4 text-sm font-semibold text-white transition hover:bg-brand-dark focus:outline-none focus:ring-2 focus:ring-brand/20">
            جستجو
        </button>
    </div>
</form>
