<header class="sticky top-0 z-40 border-b border-line/80 bg-white/90 backdrop-blur-md">
    <div class="ps-container flex h-16 items-center justify-between">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5">
            <span class="flex size-9 items-center justify-center rounded-xl bg-brand text-sm font-bold text-white shadow-sm">پ</span>
            <span class="text-base font-bold text-ink">{{ 'پارتس‌مال' }}</span>
        </a>
        <nav class="flex items-center gap-1 text-sm text-ink-muted">
            <a href="{{ route('companies.index') }}" class="hidden rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink sm:inline">خودروها</a>
            <a href="{{ route('parts.index') }}" class="hidden rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink sm:inline">قطعات</a>
            <a href="{{ route('shops.index') }}" class="hidden rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink sm:inline">فروشگاه‌ها</a>
            @foreach ($navigationPages ?? [] as $navPage)
                <a href="{{ route('page.show', $navPage->slug) }}" class="rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink">
                    {{ $navPage->title }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
