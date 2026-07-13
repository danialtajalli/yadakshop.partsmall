@props([
    'signupUrl' => route('page.show', 'register'),
])

<article {{ $attributes->merge(['class' => 'group relative overflow-hidden rounded-2xl border border-line bg-white shadow-card']) }}>
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-bl from-brand-soft/12 via-white to-surface/60" aria-hidden="true"></div>
    <div class="pointer-events-none absolute -end-10 -top-10 size-28 rounded-full bg-brand-soft/20 blur-2xl transition duration-500 group-hover:bg-brand-soft/30" aria-hidden="true"></div>
    <div
        class="pointer-events-none absolute inset-0 opacity-[0.35]"
        style="background-image: linear-gradient(to right, rgb(226 232 240 / 0.7) 1px, transparent 1px), linear-gradient(to bottom, rgb(226 232 240 / 0.7) 1px, transparent 1px); background-size: 18px 18px;"
        aria-hidden="true"
    ></div>

    <div class="relative p-5">
        <div class="mb-4 flex items-start gap-3.5">
            <div class="flex size-11 shrink-0 items-center justify-center rounded-2xl border border-brand/10 bg-white/80 text-brand shadow-sm backdrop-blur-sm">
                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-9 0H2.25A2.25 2.25 0 0 1 0 18.75V9.75A2.25 2.25 0 0 1 2.25 7.5h5.379a2.25 2.25 0 0 1 1.59.659l3.122 3.122a2.25 2.25 0 0 0 1.59.659H21.75A2.25 2.25 0 0 1 24 13.5v5.25A2.25 2.25 0 0 1 21.75 21H13.5Z" />
                </svg>
            </div>

            <div class="min-w-0 pt-0.5">
                <p class="ps-section-label mb-1.5">ثبت‌نام فروشندگان</p>
                <h3 class="text-base font-bold leading-snug tracking-tight text-ink">
                    قطعات خود را در پارتس‌مال بفروشید
                </h3>
            </div>
        </div>

        <p class="text-sm leading-7 text-ink-muted">
            فروشگاه دارید؟ قطعات خودرو را به هزاران خریدار معرفی کنید و فروش خود را افزایش دهید.
        </p>

        <a
            href="{{ $signupUrl }}"
            class="ps-btn-primary mt-5 w-full shadow-sm transition duration-200 group-hover:shadow-md"
        >
            همین حالا ثبت‌نام کنید
            <svg class="size-4 transition duration-200 group-hover:-translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7l-7-7 7-7" />
            </svg>
        </a>
    </div>
</article>
