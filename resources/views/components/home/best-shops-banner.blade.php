@props([
    'shops' => collect(),
    'signupUrl' => null,
])

@php
    $signupUrl = $signupUrl ?? route('page.show', ['slug' => 'register']);
@endphp

<section
    {{ $attributes->merge(['class' => 'mb-12']) }}
    aria-labelledby="home-best-shops-title"
    data-best-shops-banner
>
    <div class="ps-best-shops relative overflow-hidden rounded-2xl border border-white/15 bg-gradient-to-bl from-brand-dark via-brand to-[#2d3748]">
        <div class="pointer-events-none absolute -end-16 -top-16 size-56 rounded-full bg-brand-soft/20 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-20 -start-10 size-48 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>

        <div class="ps-best-shops__circuit pointer-events-none absolute inset-0 z-[15]" aria-hidden="true">
            <div class="ps-best-shops__circuit-grid"></div>
            <svg class="ps-best-shops__circuit-svg absolute inset-0 size-full overflow-visible" data-best-shops-circuit>
                <defs>
                    <filter id="ps-best-shops-beam-glow" x="-40%" y="-40%" width="180%" height="180%">
                        <feGaussianBlur stdDeviation="2.4" result="blur" />
                        <feMerge>
                            <feMergeNode in="blur" />
                            <feMergeNode in="SourceGraphic" />
                        </feMerge>
                    </filter>
                </defs>
                <path
                    class="ps-best-shops__beam-path"
                    fill="none"
                    stroke="rgb(255 220 175)"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    filter="url(#ps-best-shops-beam-glow)"
                    data-best-shops-beam
                />
                <path
                    class="ps-best-shops__beam-path ps-best-shops__beam-path--border"
                    fill="none"
                    stroke="rgb(255 220 175)"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    filter="url(#ps-best-shops-beam-glow)"
                    data-best-shops-beam-up
                />
                <path
                    class="ps-best-shops__beam-path ps-best-shops__beam-path--border"
                    fill="none"
                    stroke="rgb(255 220 175)"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    filter="url(#ps-best-shops-beam-glow)"
                    data-best-shops-beam-down
                />
            </svg>
            <div class="ps-best-shops__frame-glow"></div>
        </div>

        <div class="relative z-10 flex flex-col items-center gap-6 px-5 py-8 sm:flex-row sm:justify-between sm:gap-10 sm:px-8 sm:py-9">
            <div class="ps-best-shops__copy min-w-0 max-w-lg text-center sm:text-start" data-best-shops-copy>
                <p class="inline-flex items-center gap-2 rounded-full border border-brand-soft/40 bg-brand-soft/10 px-3 py-1 text-[11px] font-semibold tracking-[0.2em] text-brand-soft">
                    <span class="size-1.5 rounded-full bg-brand-soft" aria-hidden="true"></span>
                    منتخب پارتس‌مال
                </p>
                <h2 id="home-best-shops-title" class="mt-3 text-2xl font-black tracking-tight text-white sm:text-3xl">
                    فروشگاه‌های برتر
                </h2>
                <p class="ps-best-shops__tagline mt-4 max-w-md text-[0.9375rem] font-light leading-8 tracking-wide text-white/75 sm:text-base">
                    ویترین ویژه فروشگاه‌هایی که بیشترین اعتماد و دیده‌شدن را در پارتس‌مال دارند.
                </p>
            </div>

            <div class="ps-best-shops__logos flex min-w-0 flex-col items-center gap-4">
                <div class="flex flex-wrap items-end justify-center gap-4" role="list">
                    <div class="ps-best-shops__trio relative inline-flex flex-wrap items-end justify-center gap-4" data-best-shops-trio>
                        @foreach ($shops as $index => $shop)
                            @php
                                $rank = $index + 1;
                            @endphp
                            <a
                                href="{{ route('shop.profile', $shop->slug) }}"
                                role="listitem"
                                class="ps-best-shop-logo group relative z-10 inline-flex flex-col items-center"
                                style="--best-shop-delay: {{ 180 + ($index * 140) }}ms"
                                title="{{ $shop->name }}"
                            >
                                <span @class([
                                    'absolute -top-2 z-20 inline-flex size-6 items-center justify-center rounded-full text-[10px] font-black text-white shadow-lg ring-2 ring-white/30 sm:size-7 sm:text-xs',
                                    'bg-brand-soft' => $rank === 1,
                                    'bg-[#c0c7d1] text-brand-dark' => $rank === 2,
                                    'bg-[#c47a3a]' => $rank === 3,
                                ])>
                                    {{ $rank }}
                                </span>
                                <span @class([
                                    'relative flex items-center justify-center overflow-hidden rounded-2xl bg-white transition duration-300 group-hover:-translate-y-1 group-hover:scale-105',
                                    'size-[4.25rem] shadow-[0_16px_40px_-12px_rgb(242_124_34_/_0.65)] ring-4 ring-brand-soft/80 sm:size-[4.75rem]' => $rank === 1,
                                    'size-16 shadow-[0_14px_34px_-12px_rgb(0_0_0_/_0.55)] ring-[3px] ring-white/70 sm:size-[4.25rem]' => $rank === 2,
                                    'size-[3.75rem] shadow-[0_12px_30px_-12px_rgb(0_0_0_/_0.5)] ring-[3px] ring-[#c47a3a]/70 sm:size-16' => $rank === 3,
                                ])>
                                    <x-ui.company-logo
                                        :name="$shop->name"
                                        :logo-url="$shop->logo ?? null"
                                        size="md"
                                        @class([
                                            '!size-12 !rounded-xl sm:!size-14' => $rank === 1,
                                            '!size-11 !rounded-xl sm:!size-12' => $rank === 2,
                                            '!size-10 !rounded-xl sm:!size-11' => $rank === 3,
                                        ])
                                    />
                                </span>
                                <span class="mt-2 max-w-[4.75rem] truncate text-center text-[10px] font-semibold text-white/85 sm:max-w-[5.5rem] sm:text-[11px]">
                                    {{ $shop->name }}
                                </span>
                            </a>
                        @endforeach
                    </div>

                    <a
                        href="{{ $signupUrl }}"
                        role="listitem"
                        class="ps-best-shop-logo ps-best-shop-logo--cta inline-flex max-w-[10.5rem] items-center gap-2.5 rounded-2xl border-2 border-dashed border-brand-soft/70 bg-brand-soft/10 px-3.5 py-3 text-start shadow-[0_12px_30px_-14px_rgb(242_124_34_/_0.55)] backdrop-blur-sm transition hover:border-brand-soft hover:bg-brand-soft/20"
                        style="--best-shop-delay: {{ 180 + ($shops->count() * 140) }}ms"
                    >
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-white shadow-md">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </span>
                        <span class="min-w-0 text-xs font-semibold leading-5 text-white">
                            لوگوی شما می‌تواند اینجا باشد
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
