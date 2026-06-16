@props([
    'name',
    'logoUrl' => null,
    'size' => 'md',
])

@php
    $containerClass = match ($size) {
        'sm' => 'mb-2 size-10',
        'lg' => 'mb-3 size-14 sm:size-16',
        default => 'mb-3 size-12 sm:size-14',
    };

    $imagePadding = match ($size) {
        'sm' => 'p-1.5',
        'lg' => 'p-2.5 sm:p-3',
        default => 'p-2 sm:p-2.5',
    };

    $fallbackClass = match ($size) {
        'sm' => 'text-sm',
        'lg' => 'text-xl',
        default => 'text-base sm:text-lg',
    };
@endphp

<div {{ $attributes->merge(['class' => "flex shrink-0 items-center justify-center overflow-hidden rounded-xl border border-line bg-white shadow-sm {$containerClass}"]) }}>
    @if ($logoUrl)
        <img
            src="{{ $logoUrl }}"
            alt="{{ $name }}"
            loading="lazy"
            decoding="async"
            class="size-full object-contain {{ $imagePadding }}"
        >
    @else
        <span class="font-bold text-brand {{ $fallbackClass }}">
            {{ mb_substr($name, 0, 1) }}
        </span>
    @endif
</div>
