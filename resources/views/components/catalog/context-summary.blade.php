@props(['context', 'clearUrl' => null])

@php
    use App\Support\CarModelLabel;
    use App\Support\CatalogUrls;

    $hasContext = $context->company || $context->car || $context->model;
@endphp

@if ($hasContext)
    <div class="mb-6 flex flex-wrap items-center gap-2 rounded-xl border border-brand/20 bg-brand-soft/40 px-4 py-3 text-sm">
        <span class="font-medium text-ink">فیلتر فعال:</span>

        @if ($context->company)
            <a
                href="{{ CatalogUrls::cars($context->company->slug) }}"
                class="rounded-lg bg-white px-2.5 py-1 text-ink transition hover:text-brand"
            >
                {{ $context->company->name }}
            </a>
        @endif

        @if ($context->car && $context->company)
            <span class="text-ink-muted" aria-hidden="true">/</span>
            <a
                href="{{ CatalogUrls::models($context->company->slug, $context->car->slug) }}"
                class="rounded-lg bg-white px-2.5 py-1 text-ink transition hover:text-brand"
            >
                {{ $context->car->name }}
            </a>
        @endif

        @if ($context->model && $context->company && $context->car)
            <span class="text-ink-muted" aria-hidden="true">/</span>
            <a
                href="{{ CatalogUrls::parts($context->company->slug, $context->car->slug, $context->model->slug) }}"
                class="rounded-lg bg-white px-2.5 py-1 text-ink transition hover:text-brand"
            >
                {{ CarModelLabel::display($context->model) }}
            </a>
        @endif

        @if ($clearUrl)
            <a href="{{ $clearUrl }}" class="ms-auto text-xs font-medium text-brand transition hover:text-brand-dark">
                پاک کردن فیلتر
            </a>
        @endif
    </div>
@endif
