@php
    $headerNavigationPages = collect($navigationPages ?? []);
    $showHeaderSearch = ! request()->routeIs('home');
@endphp

<header class="sticky top-0 z-40 border-b border-line/80 bg-white/90 backdrop-blur-md">
    <div class="ps-container flex h-16 items-center justify-between gap-4">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5">
            <img src="https://partsmall.ir/img/favicon.webp" class="size-9 text-brand" alt="پارتس‌مال">
            <span class="text-base font-bold text-ink">{{ 'پارتس‌مال' }}</span>
        </a>

        <div class="hidden min-w-0 flex-1 items-center justify-end gap-3 sm:flex">
            @if ($showHeaderSearch)
                <x-ui.global-search-bar
                    id="header-meilisearch-parts-search"
                    class="mb-0 w-full max-w-sm"
                />
            @endif

            <nav class="flex shrink-0 items-center gap-1 text-sm text-ink-muted" aria-label="منوی اصلی">
                <a href="{{ route('companies.index') }}" class="rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink">کمپانی ها</a>
                <a href="{{ route('car.parts') }}" class="rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink">قطعات</a>
                <a href="{{ route('shops.index') }}" class="rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink">فروشگاه‌ها</a>
                @foreach ($headerNavigationPages as $navPage)
                    <a href="{{ route('page.show', $navPage->slug) }}" class="rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink">
                        {{ $navPage->title }}
                    </a>
                @endforeach
            </nav>
        </div>

        <button
            type="button"
            class="inline-flex size-10 items-center justify-center rounded-xl border border-line bg-white text-ink shadow-card transition hover:border-brand/30 hover:bg-brand-soft/40 sm:hidden"
            data-mobile-menu-toggle
            aria-controls="mobile-menu"
            aria-expanded="false"
            aria-label="باز کردن منو"
        >
            <span class="relative size-5" aria-hidden="true">
                <span data-mobile-menu-line="top" class="absolute inset-x-0 top-1 block h-0.5 rounded-full bg-current transition duration-200 ease-out"></span>
                <span data-mobile-menu-line="middle" class="absolute inset-x-0 top-1/2 block h-0.5 -translate-y-1/2 rounded-full bg-current transition duration-150 ease-out"></span>
                <span data-mobile-menu-line="bottom" class="absolute inset-x-0 bottom-1 block h-0.5 rounded-full bg-current transition duration-200 ease-out"></span>
            </span>
        </button>
    </div>

    <nav
        id="mobile-menu"
        class="max-h-0 overflow-hidden border-t border-line bg-white opacity-0 -translate-y-2 transition-all duration-300 ease-out sm:hidden"
        data-mobile-menu
        aria-label="منوی موبایل"
        aria-hidden="true"
    >
        <div class="ps-container grid gap-1 py-3 text-sm text-ink-muted">
            @if ($showHeaderSearch)
                <x-ui.global-search-bar
                    id="mobile-meilisearch-parts-search"
                    class="mb-3"
                />
            @endif

            <a href="{{ route('companies.index') }}" class="rounded-xl px-3 py-2.5 transition hover:bg-surface hover:text-ink">کمپانی ها</a>
            <a href="{{ route('car.parts') }}" class="rounded-xl px-3 py-2.5 transition hover:bg-surface hover:text-ink">قطعات</a>
            <a href="{{ route('shops.index') }}" class="rounded-xl px-3 py-2.5 transition hover:bg-surface hover:text-ink">فروشگاه‌ها</a>
            @foreach ($headerNavigationPages as $navPage)
                <a href="{{ route('page.show', $navPage->slug) }}" class="rounded-xl px-3 py-2.5 transition hover:bg-surface hover:text-ink">
                    {{ $navPage->title }}
                </a>
            @endforeach
        </div>
    </nav>
</header>

@once
    @push('scripts')
        <script>
            (function () {
                const toggle = document.querySelector('[data-mobile-menu-toggle]');
                const menu = document.querySelector('[data-mobile-menu]');

                if (!toggle || !menu) {
                    return;
                }

                toggle.addEventListener('click', function () {
                    const isOpen = toggle.getAttribute('aria-expanded') === 'true';
                    const nextIsOpen = !isOpen;

                    toggle.setAttribute('aria-expanded', String(nextIsOpen));
                    toggle.setAttribute('aria-label', isOpen ? 'باز کردن منو' : 'بستن منو');
                    menu.setAttribute('aria-hidden', String(isOpen));
                    menu.classList.toggle('max-h-0', isOpen);
                    menu.classList.toggle('max-h-96', nextIsOpen);
                    menu.classList.toggle('opacity-0', isOpen);
                    menu.classList.toggle('opacity-100', nextIsOpen);
                    menu.classList.toggle('-translate-y-2', isOpen);
                    menu.classList.toggle('translate-y-0', nextIsOpen);

                    toggle.querySelector('[data-mobile-menu-line="top"]')?.classList.toggle('translate-y-[7px]', nextIsOpen);
                    toggle.querySelector('[data-mobile-menu-line="top"]')?.classList.toggle('rotate-45', nextIsOpen);
                    toggle.querySelector('[data-mobile-menu-line="middle"]')?.classList.toggle('opacity-0', nextIsOpen);
                    toggle.querySelector('[data-mobile-menu-line="bottom"]')?.classList.toggle('-translate-y-[7px]', nextIsOpen);
                    toggle.querySelector('[data-mobile-menu-line="bottom"]')?.classList.toggle('-rotate-45', nextIsOpen);
                });
            })();
        </script>
    @endpush
@endonce
