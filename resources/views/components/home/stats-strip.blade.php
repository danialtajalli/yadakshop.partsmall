@props([
    'stats' => null,
])

@php
    $stats = $stats ?? config('partsmall.home_stats', []);
@endphp

@if ($stats !== [])
    <section
        {{ $attributes->merge(['class' => 'mb-12']) }}
        aria-label="آمار پارتس‌مال"
    >
        <div
            data-stats-strip
            class="ps-stats-strip relative overflow-hidden rounded-2xl shadow-[0_24px_60px_-32px_rgb(15_23_42_/_0.6)] ring-1 ring-white/10"
        >
            <div class="absolute inset-0 bg-[radial-gradient(120%_90%_at_100%_0%,rgb(242_124_34_/_0.3),transparent_52%),radial-gradient(85%_75%_at_0%_100%,rgb(255_255_255_/_0.07),transparent_48%),linear-gradient(150deg,#141b26_0%,#3a4556_46%,#1e2633_100%)]" aria-hidden="true"></div>

            <div
                class="pointer-events-none absolute inset-0 opacity-[0.14]"
                style="background-image: linear-gradient(125deg, rgb(255 255 255 / 0.4) 0%, transparent 38%, transparent 62%, rgb(242 124 34 / 0.22) 100%);"
                aria-hidden="true"
            ></div>

            <div class="pointer-events-none absolute inset-[1px] rounded-[0.9rem] ring-1 ring-inset ring-white/5" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-x-6 top-0 h-px bg-gradient-to-l from-transparent via-brand-soft/80 to-transparent sm:inset-x-10" aria-hidden="true"></div>
            <div class="pointer-events-none absolute inset-x-6 bottom-0 h-px bg-gradient-to-l from-transparent via-white/25 to-transparent sm:inset-x-10" aria-hidden="true"></div>

            <div class="relative px-4 py-9 sm:px-7 sm:py-11 lg:px-10 lg:py-12">
                <div class="ps-stats-strip__intro mb-8 text-center sm:mb-10">
                    <p class="text-[11px] font-semibold tracking-[0.28em] text-brand-soft sm:text-xs">
                        اعتباری به وسعت یک بازار
                    </p>
                    <div class="mx-auto mt-3.5 h-px w-20 bg-gradient-to-l from-transparent via-brand-soft to-transparent" aria-hidden="true"></div>
                    <p class="mx-auto mt-3 max-w-md text-xs leading-6 text-white/55 sm:text-sm">
                        شبکه‌ای از فروشگاه‌ها، قطعات و تراکنش‌های واقعی — هر روز در پارتس‌مال
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-y-9 sm:grid-cols-4 sm:gap-0">
                    @foreach ($stats as $index => $stat)
                        <div
                            data-stats-item
                            @class([
                                'ps-stats-strip__item relative px-3 text-center sm:px-5 lg:px-6',
                                'sm:border-s sm:border-white/10' => $index > 0,
                            ])
                        >
                            <p class="text-[1.85rem] font-bold leading-none tracking-tight text-white tabular-nums sm:text-3xl lg:text-[2.75rem]">
                                <span
                                    data-stats-value
                                    data-target="{{ (int) ($stat['value'] ?? 0) }}"
                                    @if (! empty($stat['live'])) data-stats-live @endif
                                >0</span>@if (! empty($stat['suffix']))<span class="text-brand-soft">{{ $stat['suffix'] }}</span>@endif
                            </p>
                            <div class="ps-stats-strip__rule mx-auto mt-3.5 h-px w-9 bg-gradient-to-l from-transparent via-brand-soft/70 to-transparent" aria-hidden="true"></div>
                            <p class="mt-3 text-[11px] font-medium tracking-wide text-white/60 sm:text-xs">
                                {{ $stat['label'] ?? '' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
@endif
