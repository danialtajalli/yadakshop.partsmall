<footer class="border-t border-line bg-white">
    <div class="ps-container py-10">
        <div class="grid gap-8 sm:grid-cols-3">
            <div>
                <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5">
                    <img src="{{ asset('panel/assets/uploads/img/favicon.webp') }}" class="size-9" alt="{{ config('app.name', 'پارتس‌مال') }}">
                    <span class="font-bold text-ink">{{ config('app.name', 'پارتس‌مال') }}</span>
                </a>
                <p class="mt-2 text-sm leading-relaxed text-ink-muted">فروشگاه‌ها، تعمیرگاه‌ها، نمایندگی‌ها و قطعات خودرو <br> همه در یکجا</p>
            </div>
            <div>
                <p class="text-sm font-semibold text-ink">دسترسی سریع</p>
                <ul class="mt-3 space-y-2 text-sm text-ink-muted">
                    <li><a href="{{ route('car.parts') }}" class="transition hover:text-brand">قطعات</a></li>
                    <li><a href="{{ route('shops.index') }}" class="transition hover:text-brand">فروشگاه‌ها</a></li>
                    @foreach ($navigationPages ?? [] as $navPage)
                        <li>
                            <a href="{{ route('page.show', $navPage->slug) }}" class="transition hover:text-brand">
                                {{ $navPage->title }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div>
                <p class="text-sm font-semibold text-ink">تماس</p>
                <p class="mt-3 text-sm text-ink-muted">info@partsmall.ir</p>
            </div>
        </div>
        <div class="mt-8 border-t border-line pt-6 text-center text-xs text-ink-muted/80" dir="rtl">
            <span>تمام حقوق این وب‌سایت برای پارتس‌مال محفوظ است.</span>
            <span class="mx-1" aria-hidden="true">©</span>
            <span dir="ltr">{{ date('Y') }}</span>
        </div>
    </div>
</footer>
