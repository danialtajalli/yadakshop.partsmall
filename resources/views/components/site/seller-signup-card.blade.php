@props([
    'signupUrl' => route('page.show', 'register'),
])

<article {{ $attributes->merge(['class' => 'ps-card overflow-hidden']) }}>
    <div class="bg-gradient-to-l from-brand-soft to-white px-5 py-4">
        <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-brand text-white shadow-sm">
            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-9 0H2.25A2.25 2.25 0 0 1 0 18.75V9.75A2.25 2.25 0 0 1 2.25 7.5h5.379a2.25 2.25 0 0 1 1.59.659l3.122 3.122a2.25 2.25 0 0 0 1.59.659H21.75A2.25 2.25 0 0 1 24 13.5v5.25A2.25 2.25 0 0 1 21.75 21H13.5Z" />
            </svg>
        </div>
        <h3 class="text-base font-bold leading-snug text-ink">
            قطعات خود را در پارتس‌مال بفروشید
        </h3>
    </div>
    <div class="px-5 py-4">
        <p class="text-sm leading-relaxed text-ink-muted">
            فروشگاه دارید؟ قطعات خودرو را به هزاران خریدار معرفی کنید و فروش خود را افزایش دهید.
        </p>
        <a
            href="{{ $signupUrl }}"
            class="ps-btn-primary mt-4 w-full"
        >
            همین حالا ثبت‌نام کنید
            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7l-7-7 7-7" />
            </svg>
        </a>
    </div>
</article>
