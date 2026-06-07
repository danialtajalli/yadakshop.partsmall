@props(['label' => null, 'title', 'description' => null])

<div {{ $attributes->merge(['class' => 'mb-6']) }}>
    @if ($label)
        <p class="ps-section-label">{{ $label }}</p>
    @endif
    <h2 class="ps-section-title {{ $label ? 'mt-1' : '' }}">{{ $title }}</h2>
    @if ($description)
        <p class="mt-1.5 text-sm text-ink-muted">{{ $description }}</p>
    @endif
</div>
