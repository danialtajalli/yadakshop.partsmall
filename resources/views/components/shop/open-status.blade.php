@props([
    'open' => false,
    'variant' => 'panel',
])

@php
    $isOpen = (bool) $open;
    $label = $isOpen ? 'اکنون باز است' : 'اکنون بسته است';
    $hint = $isOpen ? 'آماده پاسخگویی و فروش' : 'خارج از ساعات کاری';
@endphp

@if ($variant === 'compact')
    <span
        {{ $attributes->merge([
            'class' => $isOpen
                ? 'ps-shop-open-status ps-shop-open-status--open inline-flex items-center gap-1.5 rounded-full border border-emerald-200/80 bg-emerald-50 px-2.5 py-1 text-[11px] font-bold text-emerald-800'
                : 'ps-shop-open-status ps-shop-open-status--closed inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-[11px] font-bold text-slate-600',
            'role' => 'status',
        ]) }}
    >
        <span class="ps-shop-open-status__dot" aria-hidden="true"></span>
        {{ $label }}
    </span>
@else
    <div
        {{ $attributes->merge([
            'class' => $isOpen
                ? 'ps-shop-open-status ps-shop-open-status--open mb-4 flex items-center gap-3 rounded-xl border border-emerald-200/90 bg-gradient-to-l from-emerald-50 via-white to-emerald-50/80 px-3.5 py-3'
                : 'ps-shop-open-status ps-shop-open-status--closed mb-4 flex items-center gap-3 rounded-xl border border-slate-200 bg-gradient-to-l from-slate-50 via-white to-slate-50/80 px-3.5 py-3',
            'role' => 'status',
        ]) }}
    >
        <span
            @class([
                'flex size-10 shrink-0 items-center justify-center rounded-full',
                'bg-emerald-100 text-emerald-700 ring-4 ring-emerald-50' => $isOpen,
                'bg-slate-100 text-slate-500 ring-4 ring-slate-50' => ! $isOpen,
            ])
            aria-hidden="true"
        >
            @if ($isOpen)
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 10.5 12 4l9 6.5" />
                    <path d="M5.5 9.75V19a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1V9.75" />
                    <path d="M9.5 20v-5.5h5V20" />
                </svg>
            @else
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 10.5 12 4l9 6.5" />
                    <path d="M5.5 9.75V19a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1V9.75" />
                    <path d="M9.5 20v-5.5h5V20" />
                    <path d="m4 4 16 16" />
                </svg>
            @endif
        </span>

        <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <span class="ps-shop-open-status__dot" aria-hidden="true"></span>
                <p @class([
                    'text-sm font-bold tracking-tight',
                    'text-emerald-800' => $isOpen,
                    'text-slate-700' => ! $isOpen,
                ])>{{ $label }}</p>
            </div>
            <p @class([
                'mt-0.5 text-[11px] leading-4',
                'text-emerald-700/75' => $isOpen,
                'text-slate-500' => ! $isOpen,
            ])>{{ $hint }}</p>
        </div>
    </div>
@endif
