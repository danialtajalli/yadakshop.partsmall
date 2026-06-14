@props(['context', 'active' => 'companies'])

@php
    use App\Support\CatalogUrls;
@endphp

<nav class="flex flex-wrap gap-2 text-sm" aria-label="مرور خودرو و قطعات">
    <a
        href="{{ CatalogUrls::companies() }}"
        @class([
            'rounded-lg px-3 py-1.5 transition',
            'bg-brand text-white' => $active === 'companies',
            'text-ink-muted hover:bg-surface hover:text-ink' => $active !== 'companies',
        ])
    >
        کمپانی ها
    </a>
    <a
        href="{{ CatalogUrls::cars($context->company?->slug) }}"
        @class([
            'rounded-lg px-3 py-1.5 transition',
            'bg-brand text-white' => $active === 'cars',
            'text-ink-muted hover:bg-surface hover:text-ink' => $active !== 'cars',
        ])
    >
        خودروها
    </a>
    <a
        href="{{ CatalogUrls::models($context->company?->slug, $context->car?->slug) }}"
        @class([
            'rounded-lg px-3 py-1.5 transition',
            'bg-brand text-white' => $active === 'models',
            'text-ink-muted hover:bg-surface hover:text-ink' => $active !== 'models',
        ])
    >
        مدل‌ها
    </a>
    <a
        href="{{ CatalogUrls::parts($context->company?->slug, $context->car?->slug, $context->model?->slug) }}"
        @class([
            'rounded-lg px-3 py-1.5 transition',
            'bg-brand text-white' => $active === 'parts',
            'text-ink-muted hover:bg-surface hover:text-ink' => $active !== 'parts',
        ])
    >
        قطعات
    </a>
</nav>
