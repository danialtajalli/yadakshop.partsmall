@props(['label' => null, 'title', 'description' => null, 'heading' => 'h2', 'moreUrl' => null, 'moreLabel' => 'مشاهده همه'])

@php
    $headingTag = in_array($heading, ['h1', 'h2'], true) ? $heading : 'h2';
@endphp

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    <div @class(['flex flex-wrap items-end justify-between gap-4' => $moreUrl])>
        <div class="min-w-0">
            @if ($label)
                <p class="ps-section-label">{{ $label }}</p>
            @endif
            <{{ $headingTag }} @class([
                'ps-section-title',
                'mt-1' => (bool) $label,
                'text-2xl font-bold tracking-tight text-ink sm:text-3xl' => $headingTag === 'h1',
            ])>{{ $title }}</{{ $headingTag }}>
            @if ($description)
                <p class="mt-1.5 text-sm text-ink-muted">{{ $description }}</p>
            @endif
        </div>
        @if ($moreUrl)
            <a href="{{ $moreUrl }}" class="ps-btn-secondary inline-flex shrink-0 items-center gap-2">
                {{ $moreLabel }}
                <svg class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7l-7-7 7-7" />
                </svg>
            </a>
        @endif
    </div>
</div>
