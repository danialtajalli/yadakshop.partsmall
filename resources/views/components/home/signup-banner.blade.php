@props([
    'signupUrl' => null,
])

@php
    $signupUrl = $signupUrl ?? route('page.show', ['slug' => 'register']);
@endphp

<section
    {{ $attributes->merge(['class' => 'mb-12 w-full']) }}
    aria-labelledby="home-signup-banner-title"
>
    <div class="relative w-full overflow-hidden bg-gradient-to-bl from-brand-dark via-brand to-[#2d3748]">
        <div class="pointer-events-none absolute -end-20 -top-20 size-64 rounded-full bg-brand-soft/25 blur-3xl sm:size-80" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-24 -start-12 size-56 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
        <div
            class="pointer-events-none absolute inset-0 opacity-[0.07]"
            style="background-image: linear-gradient(to right, rgb(255 255 255 / 0.5) 1px, transparent 1px), linear-gradient(to bottom, rgb(255 255 255 / 0.5) 1px, transparent 1px); background-size: 28px 28px;"
            aria-hidden="true"
        ></div>

        <div class="ps-container relative z-10 py-10 sm:py-14">
            <div class="max-w-3xl">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-brand-soft">ثبت‌نام در پارتس‌مال</p>
                <h2 id="home-signup-banner-title" class="text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    دیده شوید، اعتماد بسازید، مشتری جذب کنید
                </h2>
                <p class="mt-3 text-sm leading-7 text-white/80 sm:text-base">
                    اگر فروشگاه لوازم یدکی، تعمیرگاه یا نمایندگی دارید، ثبت‌نام در پارتس‌مال ساده‌ترین راه معرفی شما به خریدارانی است که همین حالا دنبال قطعه می‌گردند. نام و اطلاعات تماس‌تان در صفحات محصول، لیست‌ها و جستجو نمایش داده می‌شود — بدون اینکه مشتری شما را گم کند.
                </p>

                <ul class="mt-5 grid gap-2.5 text-sm text-white/75 sm:grid-cols-2">
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 size-4 shrink-0 text-brand-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>نمایش در کنار قطعات و خودروهای مرتبط</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 size-4 shrink-0 text-brand-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>دسترسی مستقیم خریدار به تماس و پروفایل شما</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 size-4 shrink-0 text-brand-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>افزایش دیده‌شدن در میان رقبای محلی</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="mt-0.5 size-4 shrink-0 text-brand-soft" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>ثبت‌نام سریع و شروع نمایش در پلتفرم</span>
                    </li>
                </ul>

                <p class="mt-6 text-sm leading-7 text-white/85">
                    همین امروز جایگاه خود را رزرو کنید و به جمع فروشگاه‌ها و خدمات‌دهندگان پارتس‌مال بپیوندید.
                </p>

                <a
                    href="{{ $signupUrl }}"
                    class="mt-5 inline-flex items-center justify-center gap-2 rounded-xl bg-[#f27c22] px-6 py-3.5 text-sm font-semibold text-white shadow-[0_8px_24px_rgb(242_124_34_/_0.4)] transition hover:bg-[#e06d15] active:scale-[0.98]"
                >
                    همین حالا ثبت‌نام کنید
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7l-7-7 7-7" />
                    </svg>
                </a>
            </div>
        </div>
    </div>
</section>
