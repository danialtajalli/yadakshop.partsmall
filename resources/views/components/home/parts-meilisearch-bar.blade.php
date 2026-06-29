@props([
    'action' => route('search.index'),
    'id' => 'home-meilisearch-parts-search',
])

<form method="GET" action="{{ $action }}" {{ $attributes }}>
    <label for="{{ $id }}" class="sr-only">جستجوی قطعه</label>
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative min-w-0 flex-1">
                <svg class="pointer-events-none absolute inset-s-4 top-1/2 size-5 -translate-y-1/2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input
                    id="{{ $id }}"
                    type="search"
                    name="q"
                    placeholder="جستجوی قطعه در پارتس‌مال..."
                    autocomplete="off"
                    class="w-full shadow-card rounded-xl border border-line bg-white py-3 pe-4 ps-12 text-sm text-ink outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
                >
            </div>
            <button type="submit" class="ps-btn-primary shrink-0">
                جستجو
            </button>
        </div>
</form>
