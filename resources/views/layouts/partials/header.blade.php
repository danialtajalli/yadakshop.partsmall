@php
    $headerNavigationPages = collect($navigationPages ?? []);
    $showHeaderSearch = ! request()->routeIs('home');

    $isCompaniesNavActive = request()->routeIs('companies.index', 'cars.index', 'models.index');
    $isPartsNavActive = request()->routeIs('car.parts', 'car.parts.vehicle', 'part.show', 'product.show');
    $isShopsNavActive = request()->routeIs('shops.index', 'shop.profile');
@endphp

<header class="sticky top-0 z-40 border-b border-line/80 bg-white/90 backdrop-blur-md">
    <div class="ps-container flex h-16 items-center justify-between gap-4">
        <a href="{{ url('/') }}" class="flex items-center gap-2.5">
            <img src="{{ asset('panel/assets/uploads/img/favicon.webp') }}" class="size-9 text-brand" alt="پارتس‌مال">
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
                <a
                    href="{{ route('home') }}"
                    @class([
                        'inline-flex items-center gap-1.5 rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink',
                        'bg-surface text-ink' => request()->routeIs('home'),
                    ])
                    @if (request()->routeIs('home')) aria-current="page" @endif
                >
                    <svg class="mb-1 size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M3 10.5 12 3l9 7.5" />
                        <path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5" />
                    </svg>
                    خانه
                </a>
                <a
                    href="{{ route('companies.index') }}"
                    @class([
                        'rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink',
                        'bg-surface text-ink' => $isCompaniesNavActive,
                    ])
                    @if ($isCompaniesNavActive) aria-current="page" @endif
                >کمپانی ها</a>
                <a
                    href="{{ route('car.parts') }}"
                    @class([
                        'rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink',
                        'bg-surface text-ink' => $isPartsNavActive,
                    ])
                    @if ($isPartsNavActive) aria-current="page" @endif
                >قطعات</a>
                <a
                    href="{{ route('shops.index') }}"
                    @class([
                        'rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink',
                        'bg-surface text-ink' => $isShopsNavActive,
                    ])
                    @if ($isShopsNavActive) aria-current="page" @endif
                >فروشگاه‌های عضو</a>
                @foreach ($headerNavigationPages as $navPage)
                    @if ($navPage->slug == 'terms' || $navPage->slug == 'guide')
                        @continue
                    @endif
                    @php($isPageNavActive = (request()->routeIs('page.show') && request()->route('slug') === $navPage->slug) || request()->routeIs('page.contact') && $navPage->slug === 'contact')
                    <a
                        href="{{ route('page.show', $navPage->slug) }}"
                        @class([
                            'rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink',
                            'bg-surface text-ink' => $isPageNavActive,
                        ])
                        @if ($isPageNavActive) aria-current="page" @endif
                    >
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
            <span data-mobile-menu-icon="open" class="relative block size-5" aria-hidden="true">
                <span class="absolute inset-x-0 top-1 block h-0.5 rounded-full bg-current"></span>
                <span class="absolute inset-x-0 top-1/2 block h-0.5 -translate-y-1/2 rounded-full bg-current"></span>
                <span class="absolute inset-x-0 bottom-1 block h-0.5 rounded-full bg-current"></span>
            </span>
            <svg
                data-mobile-menu-icon="close"
                class="hidden size-5"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                aria-hidden="true"
            >
                <path d="M6 6l12 12M18 6 6 18" />
            </svg>
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

            <a
                href="{{ route('home') }}"
                @class([
                    'inline-flex items-center gap-1.5 rounded-lg px-3 py-2 transition hover:bg-surface hover:text-ink',
                    'bg-surface text-ink' => request()->routeIs('home'),
                ])
                @if (request()->routeIs('home')) aria-current="page" @endif
            >
                <svg class="size-4 mb-1 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 10.5 12 3l9 7.5" />
                    <path d="M5 9.5V20a1 1 0 0 0 1 1h4v-6h4v6h4a1 1 0 0 0 1-1V9.5" />
                </svg>
                خانه
            </a>
            <a
                href="{{ route('companies.index') }}"
                @class([
                    'rounded-xl px-3 py-2.5 transition hover:bg-surface hover:text-ink',
                    'bg-surface text-ink' => $isCompaniesNavActive,
                ])
                @if ($isCompaniesNavActive) aria-current="page" @endif
            >کمپانی ها</a>
            <a
                href="{{ route('car.parts') }}"
                @class([
                    'rounded-xl px-3 py-2.5 transition hover:bg-surface hover:text-ink',
                    'bg-surface text-ink' => $isPartsNavActive,
                ])
                @if ($isPartsNavActive) aria-current="page" @endif
            >قطعات</a>
            <a
                href="{{ route('shops.index') }}"
                @class([
                    'rounded-xl px-3 py-2.5 transition hover:bg-surface hover:text-ink',
                    'bg-surface text-ink' => $isShopsNavActive,
                ])
                @if ($isShopsNavActive) aria-current="page" @endif
            >فروشگاه‌ها</a>
            @foreach ($headerNavigationPages as $navPage)
                @if ($navPage->slug == 'terms' || $navPage->slug == 'guide')
                    @continue
                @endif
                @php($isPageNavActive = (request()->routeIs('page.show') && request()->route('slug') === $navPage->slug) || request()->routeIs('page.contact') && $navPage->slug === 'contact')
                <a
                    href="{{ route('page.show', $navPage->slug) }}"
                    @class([
                        'rounded-xl px-3 py-2.5 transition hover:bg-surface hover:text-ink',
                        'bg-surface text-ink' => $isPageNavActive,
                    ])
                    @if ($isPageNavActive) aria-current="page" @endif
                >
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
                    const openIcon = toggle.querySelector('[data-mobile-menu-icon="open"]');
                    const closeIcon = toggle.querySelector('[data-mobile-menu-icon="close"]');

                    toggle.setAttribute('aria-expanded', String(nextIsOpen));
                    toggle.setAttribute('aria-label', nextIsOpen ? 'بستن منو' : 'باز کردن منو');
                    menu.setAttribute('aria-hidden', String(!nextIsOpen));
                    menu.classList.toggle('max-h-0', !nextIsOpen);
                    menu.classList.toggle('max-h-96', nextIsOpen);
                    menu.classList.toggle('opacity-0', !nextIsOpen);
                    menu.classList.toggle('opacity-100', nextIsOpen);
                    menu.classList.toggle('-translate-y-2', !nextIsOpen);
                    menu.classList.toggle('translate-y-0', nextIsOpen);

                    openIcon?.classList.toggle('hidden', nextIsOpen);
                    closeIcon?.classList.toggle('hidden', !nextIsOpen);
                });
            })();
        </script>
    @endpush
@endonce
