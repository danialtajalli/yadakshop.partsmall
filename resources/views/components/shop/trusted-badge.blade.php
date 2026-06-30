@props([
    'label' => 'فروشگاه مورد اعتماد',
    'compact' => false,
])

<span
    {{ $attributes->merge([
        'class' => $compact
            ? 'inline-flex shrink-0 items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-1.5 py-0.5 text-[10px] font-bold text-emerald-700 shadow-sm'
            : 'inline-flex shrink-0 items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 shadow-sm',
    ]) }}
>
    <svg
        class="{{ $compact ? 'size-3' : 'size-4' }}"
        viewBox="0 0 24 24"
        fill="none"
        aria-hidden="true"
    >
        <path
            d="M12 3.25 18.25 5.5v5.35c0 4.05-2.55 7.7-6.25 9.05-3.7-1.35-6.25-5-6.25-9.05V5.5L12 3.25Z"
            fill="currentColor"
            opacity=".16"
        />
        <path
            d="M12 3.25 18.25 5.5v5.35c0 4.05-2.55 7.7-6.25 9.05-3.7-1.35-6.25-5-6.25-9.05V5.5L12 3.25Z"
            stroke="currentColor"
            stroke-width="1.8"
            stroke-linejoin="round"
        />
        <path
            d="m8.9 11.65 2.05 2.05 4.35-4.55"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
    <span>{{ $label }}</span>
</span>
