@props([
    'title' => 'به گروه تلگرام چری تیگو ۵ سواران بپیوندید',
    'telegramUrl' => config('partsmall.telegram_url'),
    'benefits' => [
        'دریافت جدیدترین اخبار و اطلاعات',
        'شرکت در بحث‌ها و تبادل نظر با دیگر اعضا',
        'دسترسی به محتوای آموزشی و منابع ارزشمند',
        'ارتباط مستقیم با کارشناسان و متخصصین',
    ],
])

<article {{ $attributes->merge(['class' => 'ps-card overflow-hidden']) }}>
    <div class="border-b border-line bg-gradient-to-l from-sky-50 to-white px-5 py-4">
        <div class="mb-3 flex size-10 items-center justify-center rounded-xl bg-[#229ED9] text-white shadow-sm">
            <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
            </svg>
        </div>
        <h3 class="text-base font-bold leading-snug text-ink">{{ $title }}</h3>
    </div>
    <div class="px-5 py-4">
        <ul class="space-y-2.5 text-sm text-ink-muted">
            @foreach ($benefits as $benefit)
                <li class="flex gap-2">
                    <svg class="mt-0.5 size-4 shrink-0 text-brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                    <span>{{ $benefit }}</span>
                </li>
            @endforeach
        </ul>
        <p class="mt-4 text-sm font-medium text-ink">بهره‌مند شوید. منتظرتان هستیم!</p>
        <a
            href="{{ $telegramUrl }}"
            target="_blank"
            rel="noopener noreferrer"
            class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-[#229ED9] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#1d8bc2] active:scale-[0.98]"
        >
            <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/>
            </svg>
            عضویت در گروه تلگرام
        </a>
    </div>
</article>
