@props(['label' => null, 'title', 'description' => null, 'heading' => 'h2'])

@php
    $headingTag = in_array($heading, ['h1', 'h2'], true) ? $heading : 'h2';
@endphp

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
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
