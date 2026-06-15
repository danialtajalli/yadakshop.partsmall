@props([
    'id' => 'catalog-search',
    'name' => null,
    'value' => '',
    'placeholder' => 'جستجو...',
    'emptyMessage' => null,
])

@php
    $emptyId = $emptyMessage ? $id.'-empty' : null;
@endphp

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    <label for="{{ $id }}" class="sr-only">{{ $placeholder }}</label>
    <div class="relative">
        <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input
            id="{{ $id }}"
            type="search"
            @if ($name) name="{{ $name }}" @endif
            value="{{ $value }}"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            class="w-full rounded-2xl border border-line bg-white py-3.5 pe-4 ps-12 text-sm text-ink shadow-card outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
        >
    </div>
    @if ($emptyMessage)
        <p id="{{ $emptyId }}" class="mt-3 hidden text-sm text-ink-muted">{{ $emptyMessage }}</p>
    @endif
</div>
