@props([
    'title',
    'shopsCount',
])

<a
    href="#shops"
    {{ $attributes->merge([
        'class' => 'ps-shops-jump group relative block w-full overflow-hidden rounded-xl border border-orange-300/35 shadow-[0_8px_30px_rgba(242,124,34,0.14)] transition duration-200 hover:border-orange-400/50 hover:shadow-[0_12px_36px_rgba(242,124,34,0.2)] active:scale-[0.99] sm:w-1/2',
    ]) }}
>
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-orange-100 via-orange-50 to-amber-50" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -right-10 -top-10 h-28 w-28 rounded-full bg-orange-300/25 blur-2xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -left-8 bottom-0 h-24 w-24 rounded-full bg-amber-200/35 blur-2xl" aria-hidden="true"></div>
    <div
        class="pointer-events-none absolute inset-0 opacity-[0.14]"
        style="background-image: linear-gradient(to right, rgb(242 124 34 / 0.45) 1px, transparent 1px), linear-gradient(to bottom, rgb(242 124 34 / 0.45) 1px, transparent 1px); background-size: 18px 18px;"
        aria-hidden="true"
    ></div>

    <span class="relative z-10 flex items-center justify-between gap-3 px-4 py-3.5 text-sm">
        <span class="flex min-w-0 items-center gap-2.5 font-medium text-ink">
            <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand text-white shadow-sm">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36a1.125 1.125 0 0 1-1.009-.69L.5 9.75A1.125 1.125 0 0 1 1.509 8.5H5.25m8.25 0V5.625A2.625 2.625 0 0 0 11.625 3h-3.75A2.625 2.625 0 0 0 5.25 5.625V8.5m8.25 0H5.25" />
                </svg>
            </span>
            <span>
                <span class="block font-semibold text-brand-dark">{{ number_format($shopsCount) }} فروشگاه مرتبط</span>
                <span class="block text-xs font-medium text-brand-dark/70">برای خرید {{ $title }} کلیک کنید</span>
            </span>
        </span>
        <span class="flex shrink-0 items-center gap-1 text-xs font-semibold text-brand">
            مشاهده
            <svg class="ps-shops-jump-chevron size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </span>
    </span>
</a>
