@props([
    'id' => 'catalog-search',
    'name' => null,
    'value' => '',
    'placeholder' => 'جستجو...',
    'emptyMessage' => null,
    'showSubmit' => false,
    'submitLabel' => 'جستجو',
    'clearUrl' => null,
    'showClear' => true,
    'alpine' => false,
])

@php
    $emptyId = $emptyMessage ? $id.'-empty' : null;
    $useClearLink = $showClear && filled($clearUrl) && ! $alpine;
    $useClearButton = $showClear && ($alpine || blank($clearUrl));
@endphp

<div {{ $attributes->merge(['class' => 'mb-6 min-w-0']) }}>
    <label for="{{ $id }}" class="sr-only">{{ $placeholder }}</label>
    <div class="relative min-w-0">
        <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
        <input
            id="{{ $id }}"
            type="search"
            @if ($name) name="{{ $name }}" @endif
            @if (! $alpine) value="{{ $value }}" @endif
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            class="w-full rounded-2xl border border-line bg-white py-3.5 pe-4 ps-12 text-sm text-ink shadow-card outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
            @if ($alpine)
                x-ref="searchInput"
                x-model="query"
                @input="onQueryInput"
                @keydown.enter.prevent="scheduleSearch({ force: true })"
            @endif
        >
    </div>

    @if (isset($between))
        <div class="mt-4">
            {{ $between }}
        </div>
    @endif

    @if ($emptyMessage)
        <x-catalog.search-empty
            :id="$emptyId"
            :message="$emptyMessage"
            :clear-button="$useClearButton"
            :alpine="$alpine"
            :hidden="! $alpine"
            @if ($alpine)
                x-cloak
                x-show="emptyVisible"
                x-bind:hidden="!emptyVisible"
            @endif
        />
    @endif

    @if ($showSubmit || $useClearLink || $useClearButton)
        <div class="mt-4 flex flex-wrap items-center gap-3">
            @if ($showSubmit)
                <button type="submit" class="ps-btn-primary">{{ $submitLabel }}</button>
            @endif
            @if ($useClearLink)
                <a href="{{ $clearUrl }}" class="ps-btn-secondary">پاک کردن</a>
            @endif
            @if ($useClearButton && $alpine)
                <button
                    type="button"
                    class="ps-btn-secondary"
                    @click="clearSearch"
                >
                    پاک کردن
                </button>
            @elseif ($useClearButton && ! $alpine)
                <button type="button" data-catalog-search-clear class="ps-btn-secondary">پاک کردن</button>
            @endif
        </div>
    @endif

    @if ($alpine)
        <p class="mt-2 text-[11px] text-ink-muted" x-cloak x-show="loading">در حال جستجو...</p>
    @endif
</div>
