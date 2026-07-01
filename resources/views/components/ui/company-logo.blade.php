@props([
    'name',
    'logoUrl' => null,
    'size' => 'md',
    'fit' => 'contain',
])

@php
    $sizeClass = match ($size) {
        'xs' => 'size-10 sm:size-11',
        'sm' => 'size-10',
        'grid' => 'size-12',
        'md' => 'size-12 sm:size-14',
        'lg' => 'size-14 sm:size-16',
        'listing' => 'size-14',
        'carousel' => 'size-[4.5rem] sm:size-20',
        'xl' => 'size-20 sm:size-24',
        default => 'size-12 sm:size-14',
    };

    $fallbackTextClass = match ($size) {
        'xs', 'sm', 'grid' => 'text-sm',
        'listing', 'carousel' => 'text-lg',
        'xl' => 'text-2xl',
        'lg' => 'text-xl',
        default => 'text-base sm:text-lg',
    };

    $fitClass = $fit === 'cover' ? 'object-cover' : 'object-contain';
@endphp

@if ($logoUrl)
    <img
        src="{{ $logoUrl }}"
        alt="{{ $name }}"
        loading="lazy"
        decoding="async"
        {{ $attributes->merge(['class' => "shrink-0 {$sizeClass} rounded-xl {$fitClass}"]) }}
    >
@else
    <div {{ $attributes->merge(['class' => "flex shrink-0 items-center justify-center overflow-hidden rounded-xl ring-1 ring-line {$sizeClass} font-bold text-brand-dark {$fallbackTextClass}"]) }}>
        {{ mb_substr($name, 0, 1) }}
    </div>
@endif
