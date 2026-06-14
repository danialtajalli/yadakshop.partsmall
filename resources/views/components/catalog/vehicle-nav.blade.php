@props(['context', 'active' => 'companies'])

@php
    $params = $context->queryParams();
@endphp

<nav class="flex flex-wrap gap-2 text-sm" aria-label="مرور خودرو و قطعات">
    <a
        href="{{ route('companies.index') }}"
        @class([
            'rounded-lg px-3 py-1.5 transition',
            'bg-brand text-white' => $active === 'companies',
            'text-ink-muted hover:bg-surface hover:text-ink' => $active !== 'companies',
        ])
    >
        کمپانی ها
    </a>
    <a
        href="{{ route('cars.index') }}"
        @class([
            'rounded-lg px-3 py-1.5 transition',
            'bg-brand text-white' => $active === 'cars',
            'text-ink-muted hover:bg-surface hover:text-ink' => $active !== 'cars',
        ])
    >
        خودروها
    </a>
    <a
        href="{{ route('models.index') }}"
        @class([
            'rounded-lg px-3 py-1.5 transition',
            'bg-brand text-white' => $active === 'models',
            'text-ink-muted hover:bg-surface hover:text-ink' => $active !== 'models',
        ])
    >
        مدل‌ها
    </a>
    <a
        href="{{ route('car.parts') }}"
        @class([
            'rounded-lg px-3 py-1.5 transition',
            'bg-brand text-white' => $active === 'parts',
            'text-ink-muted hover:bg-surface hover:text-ink' => $active !== 'parts',
        ])
    >
        قطعات
    </a>
</nav>
