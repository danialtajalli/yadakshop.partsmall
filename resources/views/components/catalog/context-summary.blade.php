@props(['context'])

@php
    use App\Support\CarModelLabel;

    $hasContext = $context->company || $context->car || $context->model;
@endphp

@if ($hasContext)
    <div class="mb-6 flex flex-wrap items-center gap-2 rounded-xl border border-brand/20 bg-brand-soft/40 px-4 py-3 text-sm">
        <span class="font-medium text-ink">فیلتر فعال:</span>

        @if ($context->company)
            <a
                href="{{ route('cars.index', ['company' => $context->company->slug]) }}"
                class="rounded-lg bg-white px-2.5 py-1 text-ink transition hover:text-brand"
            >
                {{ $context->company->name }}
            </a>
        @endif

        @if ($context->car && $context->company)
            <span class="text-ink-muted" aria-hidden="true">/</span>
            <a
                href="{{ route('models.index', ['company' => $context->company->slug, 'car' => $context->car->slug]) }}"
                class="rounded-lg bg-white px-2.5 py-1 text-ink transition hover:text-brand"
            >
                {{ $context->car->name }}
            </a>
        @endif

        @if ($context->model && $context->company && $context->car)
            <span class="text-ink-muted" aria-hidden="true">/</span>
            <a
                href="{{ route('car.parts', ['company' => $context->company->slug, 'car' => $context->car->slug, 'model' => $context->model->slug]) }}"
                class="rounded-lg bg-white px-2.5 py-1 text-ink transition hover:text-brand"
            >
                {{ CarModelLabel::display($context->model) }}
            </a>
        @endif

        <a href="{{ route($attributes->get('clear-route', 'companies.index')) }}" class="ms-auto text-xs font-medium text-brand transition hover:text-brand-dark">
            پاک کردن فیلتر
        </a>
    </div>
@endif
