@props([
    'message',
    'clearUrl' => null,
    'clearButton' => false,
    'clearLabel' => 'پاک کردن',
    'clearDataAttribute' => 'data-catalog-search-clear',
    'id' => null,
    'boxed' => false,
    'hidden' => false,
])

@php
    $containerClass = $boxed
        ? 'rounded-2xl border border-dashed border-line bg-white px-6 py-12'
        : 'mt-3';

    $innerClass = $boxed
        ? 'flex flex-wrap items-center justify-center gap-3'
        : 'flex flex-wrap items-center gap-3';
@endphp

<div
    @if ($id) id="{{ $id }}" @endif
    {{ $attributes->class([$containerClass, 'hidden' => $hidden]) }}
>
    <div class="{{ $innerClass }}">
        <p class="text-sm text-ink-muted">{{ $message }}</p>

        @if ($clearUrl)
            <a href="{{ $clearUrl }}" class="text-sm font-medium text-brand transition hover:text-brand-dark">{{ $clearLabel }}</a>
        @elseif ($clearButton)
            <button
                type="button"
                {{ $clearDataAttribute }}
                class="text-sm font-medium text-brand transition hover:text-brand-dark"
            >
                {{ $clearLabel }}
            </button>
        @endif
    </div>
</div>
