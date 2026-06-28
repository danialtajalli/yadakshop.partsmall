@props([
    'title' => 'به گروه تلگرام چری تیگو ۵ سواران بپیوندید',
    'telegramUrl' => config('partsmall.telegram_url'),
])

<article
    {{ $attributes->merge([
        'class' => 'group relative overflow-hidden rounded-3xl border border-white/20 bg-gradient-to-br from-sky-500 via-cyan-500 to-blue-600 text-white shadow-[0_20px_60px_rgba(14,165,233,0.35)]',
    ]) }}
>
    <!-- Background glow -->
    <div class="absolute -right-16 -top-16 h-40 w-40 rounded-full bg-white/15 blur-3xl"></div>
    <div class="absolute -left-12 bottom-0 h-32 w-32 rounded-full bg-cyan-300/20 blur-3xl"></div>

    <!-- Decorative grid -->
    <div
        class="absolute inset-0 opacity-10"
        style="
            background-image:
                linear-gradient(to right, white 1px, transparent 1px),
                linear-gradient(to bottom, white 1px, transparent 1px);
            background-size: 20px 20px;
        "
    ></div>

    <!-- Car silhouette -->
    <svg
        class="absolute -left-8 bottom-0 h-28 w-48 opacity-10"
        viewBox="0 0 300 120"
        fill="currentColor"
        aria-hidden="true"
    >
        <path d="M48 79c7-20 20-33 41-40l44-15c12-4 28-4 40 0l40 12c16 5 27 17 34 36h14c8 0 15 7 15 15v8h-18a20 20 0 0 1-40 0H108a20 20 0 0 1-40 0H30v-8c0-5 2-8 6-8h12z"/>
    </svg>

    <div class="relative p-6">

        <!-- Badge -->
        <div class="mb-4">
            <span
                class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/15 px-3 py-1 text-xs font-bold backdrop-blur-md"
            >
                🔥 بزرگ‌ترین جامعه کاربران تیگو ۵
            </span>
        </div>

        <!-- Header -->
        <div class="flex items-start gap-4">

            <!-- Telegram icon with pulse -->
            <div class="relative shrink-0">

                <span
                    class="absolute inset-0 animate-ping rounded-2xl bg-white/30"
                ></span>

                <div
                    class="relative flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-[#229ED9] shadow-xl"
                >
                    <svg class="h-7 w-7" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
                    </svg>
                </div>

            </div>

            <div>
                <h3 class="text-xl font-black leading-relaxed">
                    {{ $title }}
                </h3>

                <p class="mt-2 text-sm leading-6 text-sky-100">
                    تبادل تجربه، آموزش تعمیرات، معرفی قطعات و پاسخ به سوالات
                    توسط هزاران مالک تیگو ۵.
                </p>
            </div>

        </div>

        <!-- Social proof -->
        <div class="mt-5 flex items-center gap-3">

            <div class="flex -space-x-2 rtl:space-x-reverse">
                <img
                    src="https://i.pravatar.cc/64?img=12"
                    alt=""
                    class="h-8 w-8 rounded-full border-2 border-white object-cover shadow-sm"
                    loading="lazy"
                >
                <img
                    src="https://i.pravatar.cc/64?img=32"
                    alt=""
                    class="h-8 w-8 rounded-full border-2 border-white object-cover shadow-sm"
                    loading="lazy"
                >
                <img
                    src="https://i.pravatar.cc/64?img=56"
                    alt=""
                    class="h-8 w-8 rounded-full border-2 border-white object-cover shadow-sm"
                    loading="lazy"
                >
            </div>

            <span class="text-sm font-medium text-white/90">
                +۳۰۰۰ عضو فعال
            </span>

        </div>

        <!-- Benefits -->
        <div class="mt-6 space-y-3">

            @foreach([
                'دریافت جدیدترین اخبار و اطلاعیه‌ها',
                'پرسش و پاسخ فنی با کاربران باتجربه',
                'معرفی قطعات و فروشندگان معتبر',
                'اشتراک تجربه‌های نگهداری خودرو',
            ] as $benefit)

                <div class="flex items-center gap-3">

                    <div class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20">
                        ✓
                    </div>

                    <span class="text-sm">
                        {{ $benefit }}
                    </span>

                </div>

            @endforeach

        </div>

        <!-- Highlight box -->
        <div
            class="mt-6 rounded-2xl border border-white/10 bg-white/10 p-4 backdrop-blur-md"
        >
            <p class="text-sm leading-6">
                💡 هر روز نکات فنی، آموزش‌های کاربردی و معرفی قطعات جدید در گروه
                منتشر می‌شود.
            </p>
        </div>

        <!-- CTA -->
        <a
            href="{{ $telegramUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="mt-6 flex w-full items-center justify-center gap-3 rounded-2xl bg-white py-3.5 text-base font-extrabold text-[#229ED9] shadow-xl transition-all duration-300 hover:-translate-y-0.5 hover:shadow-2xl"
        >
            <span>عضویت در گروه تلگرام</span>

            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
            </svg>
        </a>

    </div>
</article>
