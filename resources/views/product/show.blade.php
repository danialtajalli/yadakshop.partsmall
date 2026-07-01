@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-site.breadcrumb :items="$breadcrumbs" />

    {{-- Title section --}}
    <div class="mb-5 rounded-2xl border border-line bg-white shadow-card" data-product-title-section>
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    @if ($part->partsCategory)
                        <span class="mb-2 inline-flex items-center rounded-full bg-brand-soft px-3 py-1 text-xs font-medium text-brand-dark">
                            {{ $part->partsCategory->name }}
                        </span>
                    @endif
                    <h1 class="text-2xl font-bold tracking-tight text-ink/80 sm:text-3xl">{{ $title }}</h1>
                </div>
            </div>

            @if ($shops->isNotEmpty())
                <div class="mt-5" data-shops-jump-anchor>
                    <x-product.shops-jump-button
                        :title="$title"
                        :shops-count="count($shops)"
                        data-shops-jump
                    />
                </div>
            @endif
        </div>
    </div>

    {{-- Service cards beside Telegram CTA --}}
    <div class="mb-10 grid gap-5 lg:grid-cols-12 lg:items-stretch">
        @if ($repairLocators || count($repairCards) > 0)
            <section class="min-w-0 overflow-hidden rounded-3xl border border-line bg-white shadow-card lg:col-span-8">
                <div class="border-b border-line bg-linear-to-l from-gray-100 via-white px-5 py-5">
                    <h2 class="mt-1 text-lg font-black text-ink/80">مشاهده تعمیرگاه‌ها و اجرت‌ها</h2>
                    <p class="mt-1.5 text-xs leading-6 text-ink-muted/70">برای همین قطعه، مسیرهای تعمیر و حدود اجرت را کنار هم ببینید.</p>
                </div>

                <div class="grid min-w-0 gap-4 p-4 md:grid-cols-2">
                    @if ($repairLocators)
                        <div class="min-w-0 rounded-2xl border border-line bg-surface/50 p-3">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <h3 class="text-sm font-bold text-ink/80">تعمیرگاه‌های مرتبط</h3>
                                <span class="text-[11px] font-medium text-ink-muted/70">{{ count($repairLocators) }} مسیر</span>
                            </div>
                            <div class="grid min-w-0 gap-2">
                                @foreach ($repairLocators as $repairLocator)
                                    <x-product.repair-locator :repair-locator="$repairLocator" />
                                @endforeach
                            </div>
                        </div>
                    @endif

                    @if (count($repairCards) > 0)
                        <div class="min-w-0 rounded-2xl border border-line bg-surface/50 p-3">
                            <div class="mb-2 flex items-center justify-between gap-3">
                                <h3 class="text-sm font-bold text-ink/80">برآورد اجرت</h3>
                                <span class="text-[11px] font-medium text-ink-muted/70">حدودی</span>
                            </div>

                            <div class="grid gap-2">
                                @foreach ($repairCards as $wage_name => $wage)
                                    <div class="rounded-xl border border-line bg-white px-3 py-2.5">
                                        <div class="flex items-start justify-between gap-3">
                                            <p class="min-w-0 text-xs font-semibold leading-5 text-ink/80">اجرت {{ $wage_name }}</p>
                                            <div class="shrink-0 text-start">
                                                @if ($wage['cost'] !== null)
                                                    <p class="text-sm font-black tabular-nums text-ink">
                                                        {{ number_format($wage['cost']) }}
                                                        <span class="text-[10px] font-medium text-ink-muted/70">تومان</span>
                                                    </p>
                                                @else
                                                    <p class="text-xs text-ink-muted/70">نامشخص</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        <div class="min-w-0 lg:col-span-4">
            <div class="h-full">
                <x-site.cta-sidebar
                    :telegram-title="'به گروه تلگرام ' . $company->name . ' ' . $car->name . ' سواران بپیوندید'"
                    :telegram-url="'https://t.me/' . $company->slug . '_saravan_partsmall'"
                />
            </div>
        </div>
    </div>

    {{-- Shops --}}
    <section id="shops" class="mb-12 scroll-mt-20 ps-shops-section">
        <div class="mb-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="ps-section-title text-2xl font-bold tracking-tight text-ink sm:text-3xl">لیست فروشگاه ها</h2>
                    <p class="mt-1.5 text-sm text-ink-muted">فروشگاه های زیر این قطعه را موجود دارند</p>
                </div>

                @if ($shops->isNotEmpty())
                    <div class="relative shrink-0" data-shops-filter>
                        <button
                            type="button"
                            class="ps-btn-secondary inline-flex items-center gap-2"
                            data-shops-filter-toggle
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-controls="shops-filter-menu"
                        >
                            فیلتر
                            <svg class="size-4 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>

                        <div
                            id="shops-filter-menu"
                            data-shops-filter-menu
                            class="absolute end-0 z-30 mt-2 hidden w-72 rounded-xl border border-line bg-white p-4 shadow-card"
                        >
                            <label class="mb-4 flex cursor-pointer items-center gap-2.5 text-sm text-ink">
                                <input
                                    type="checkbox"
                                    data-shops-filter-verified
                                    class="size-4 rounded border-line text-brand focus:ring-brand/30"
                                >
                                <span>فقط فروشگاه مورد اعتماد</span>
                            </label>

                            <div>
                                <label for="shops-filter-state" class="mb-1.5 block text-xs font-medium text-ink-muted">شهر</label>
                                <select
                                    id="shops-filter-state"
                                    data-shops-filter-state
                                    class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none transition focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
                                >
                                    <option value="">همه شهرها</option>
                                    @foreach ($shopFilterStates as $state)
                                        <option value="{{ $state->id }}">{{ $state->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        @if ($shops->isNotEmpty())
            <div
                data-shops-filter-empty
                class="mb-4 hidden rounded-xl border border-dashed border-line bg-white px-6 py-10 text-center"
            >
                <p class="text-sm text-ink-muted">فروشگاهی با این فیلتر یافت نشد.</p>
            </div>

            <div class="grid gap-2.5 sm:grid-cols-1 lg:grid-cols-1" data-shops-list data-initial-visible="10" data-batch-size="10">
                @foreach ($shops as $shopIndex => $shop)
                    <article
                        class="ps-card-interactive relative flex flex-col gap-3 p-3 sm:flex-row sm:items-center sm:p-3.5 {{ $shopIndex >= 10 ? 'hidden sm:flex' : '' }}"
                        data-shop-card
                        data-shop-state-id="{{ $shop->state_id }}"
                        data-shop-verified="{{ $shop->verified ? '1' : '0' }}"
                    >
                        <div class="flex min-w-0 flex-1 items-center gap-3">
                            <x-ui.company-logo
                                :name="$shop->name"
                                :logo-url="$shop->logo ?? null"
                                size="xs"
                            />
                            <div class="min-w-0 flex-1">
                                <div class="flex min-w-0 flex-wrap items-center gap-x-2 gap-y-1">
                                    <h3 class="truncate text-sm font-semibold text-ink">{{ $shop->name }}</h3>
                                    @if ($shop->verified)
                                        <x-shop.trusted-badge compact />
                                    @endif
                                    <div class="inline-flex shrink-0 items-center gap-1 rounded-md bg-accent-soft px-1.5 py-0.5 text-[11px] font-medium text-accent">
                                        @if ($shop->average_rating)
                                            <svg class="size-3 fill-current" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z"/></svg>
                                            <span>{{ number_format($shop->average_rating, 1) }}</span>
                                        @else
                                            <span class="text-accent">بدون امتیاز</span>
                                        @endif
                                    </div>
                                </div>
                                @if ($shop->secondary_name)
                                    <p class="truncate text-xs text-ink-muted/70">{{ $shop->secondary_name }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex gap-2 sm:w-auto sm:shrink-0">
                            <a href="{{ route('shop.profile', $shop->slug) }}" class="ps-btn-primary relative z-20 flex-1 px-2.5 py-1.5 text-center text-xs sm:flex-none">مشاهده پروفایل فروشگاه</a>
                            <button
                                type="button"
                                class="ps-btn-secondary shrink-0 flex-1 px-2.5 py-1.5 text-xs sm:flex-none"
                                onclick="document.getElementById('shop-modal-{{ $shop->id }}').showModal()"
                            >
                                اطلاعات تماس
                            </button>
                        </div>

                        <dialog
                            id="shop-modal-{{ $shop->id }}"
                            class="fixed inset-0 z-50 m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-line bg-white p-0 shadow-2xl backdrop:bg-ink/40 open:animate-none"
                        >
                            <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4">
                                <h4 class="font-bold text-ink">{{ $shop->name }}</h4>
                                <button
                                    type="button"
                                    class="flex size-8 items-center justify-center rounded-lg text-ink-muted/70 transition hover:bg-surface hover:text-ink/80"
                                    onclick="document.getElementById('shop-modal-{{ $shop->id }}').close()"
                                    aria-label="بستن"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="max-h-[70vh] space-y-5 overflow-y-auto px-5 py-5 text-sm">
                                @if ($shop->description)
                                <div class="border-t border-line px-5 py-6 sm:px-6">
                                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-brand">معرفی {{ $part->name }} {{ $company->name }} {{ $car->name }}</p>
                                        {!! $shop->description !!}
                                </div>
                                @endif

                                @if ($shop->address)
                                    <div class="rounded-xl bg-surface p-4">
                                        <p class="mb-1 text-xs font-semibold text-ink/80">آدرس</p>
                                        <p class="text-ink-muted/70">{{ $shop->address }}</p>
                                    </div>
                                @endif

                                @if ($shop->phones->isNotEmpty())
                                    <div>
                                        <p class="mb-2 text-xs font-semibold text-ink/80">تلفن‌ها</p>
                                        <x-ui.phone-icons :phones="$shop->phones" />
                                    </div>
                                @endif

                                @if ($shop->links->isNotEmpty())
                                    <div>
                                        <p class="mb-2 text-xs font-semibold text-ink/80">شبکه‌های اجتماعی</p>
                                        <x-ui.social-icons :links="$shop->links" />
                                    </div>
                                @endif

                                @if ($shop->open_time && $shop->close_time)
                                    <div class="rounded-xl bg-surface p-4">
                                        <p class="mb-1 text-xs font-semibold text-ink">ساعات کاری</p>
                                        <p class="tabular-nums text-ink-muted/70" dir="ltr">{{ $shop->open_time }} – {{ $shop->close_time }}</p>
                                    </div>
                                @endif
                            </div>
                        </dialog>
                    </article>
                @endforeach

                <article
                    class="ps-card-interactive relative flex flex-col gap-3 border-dashed p-3 sm:flex-row sm:items-center sm:p-3.5 {{ count($shops) > 10 ? 'hidden sm:flex' : '' }}"
                    data-shop-signup-card
                >
                    <div class="flex min-w-0 flex-1 items-center gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-xl border border-brand/25 bg-brand-soft/20 text-brand sm:size-11">
                            <svg class="size-6" viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="6" y="7" width="20" height="18" rx="4" />
                                <path d="M11 20h10" />
                                <path d="M12 16l3-3 3 3 2-2 3 3" />
                                <circle cx="12" cy="12" r="1.5" fill="currentColor" stroke="none" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <h3 class="truncate text-sm font-semibold text-ink">فروشگاه شما میتواند اینجا باشد</h3>
                            <p class="truncate text-xs text-ink-muted/70">قطعات خود را در پارتس‌مال معرفی کنید.</p>
                        </div>
                    </div>

                    <div class="flex gap-2 sm:w-auto sm:shrink-0">
                        <a href="{{ route('page.show', ['slug' => 'register']) }}" class="ps-btn-primary relative z-20 flex-1 px-2.5 py-1.5 text-center text-xs sm:flex-none">
                            ثبت نام
                        </a>
                    </div>
                </article>
            </div>

            @if (count($shops) > 10)
                <div class="mt-5 flex justify-center sm:hidden" data-shops-load-more-wrap>
                    <button
                        type="button"
                        class="ps-btn-secondary min-w-44 justify-center"
                        data-shops-load-more
                    >
                        نمایش بیشتر
                    </button>
                </div>
            @endif
        @else
            <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
                <p class="text-sm text-ink-muted/70">فروشگاهی برای این قطعه یافت نشد.</p>
            </div>
        @endif
    </section>

    {{-- Part specs --}}
    <section>
        <x-ui.section-heading label="جزئیات" title="مشخصات قطعه" />

        <div class="ps-card overflow-hidden">
            <dl class="divide-y divide-line">
                @foreach ([
                    'نام خودرو' => $car->name,
                    'نام قطعه' => $part->name,
                    'مدل خودرو' => $model->name,
                    'شرکت سازنده' => $company->name,
                    'کشور سازنده' => $company->country ?? '—',
                    'نام لاتین خودرو' => $car->slug,
                    'نام لاتین قطعه' => $part->slug,
                ] as $label => $value)
                    <div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6 even:bg-surface/50">
                        <dt class="text-sm font-medium text-ink-muted/70">{{ $label }}</dt>
                        <dd @class([
                            'text-sm font-semibold text-ink sm:col-span-2',
                            'font-mono font-normal text-ink-muted/70' => str_contains($label, 'اسلاگ'),
                        ])>{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($part->description)
                <div class="border-t border-line px-5 py-6 sm:px-6">
                    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-brand">معرفی {{ $part->name }} {{ $company->name }} {{ $car->name }}</h2>
                    <x-ui.expandable-description id="part-description">
                        {!! $part->description !!}
                    </x-ui.expandable-description>
                </div>
            @endif

            @if ($car->description)
                <div class="border-t border-line px-5 py-6 sm:px-6">
                    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-brand">معرفی خودرو {{ $company->name }} {{ $car->name }}</h2>
                    <x-ui.expandable-description id="car-description">
                        {!! $car->description !!}
                    </x-ui.expandable-description>
                </div>
            @endif
        </div>
    </section>

    @if ($shops->isNotEmpty())
        @push('overlays')
            <div
                data-shops-jump-fixed
                class="pointer-events-none fixed inset-x-4 top-20 z-50 opacity-0 transition-opacity duration-200 sm:hidden"
                aria-hidden="true"
            >
                <x-product.shops-jump-button
                    :title="$title"
                    :shops-count="count($shops)"
                    data-shops-jump-fixed-link
                    class="!w-full sm:!w-full"
                />
            </div>
        @endpush

        @push('scripts')
            <script>
                (function () {
                    const shopsList = document.querySelector('[data-shops-list]');
                    const loadMore = document.querySelector('[data-shops-load-more]');
                    const loadMoreWrap = document.querySelector('[data-shops-load-more-wrap]');
                    const signupCard = document.querySelector('[data-shop-signup-card]');
                    const filterRoot = document.querySelector('[data-shops-filter]');
                    const filterToggle = document.querySelector('[data-shops-filter-toggle]');
                    const filterMenu = document.querySelector('[data-shops-filter-menu]');
                    const filterState = document.querySelector('[data-shops-filter-state]');
                    const filterVerified = document.querySelector('[data-shops-filter-verified]');
                    const filterEmpty = document.querySelector('[data-shops-filter-empty]');
                    const shopCards = shopsList
                        ? Array.from(shopsList.querySelectorAll('[data-shop-card]'))
                        : [];
                    const initialBatchSize = Number(shopsList?.dataset.batchSize || 10);
                    const mobileMedia = window.matchMedia('(max-width: 639px)');

                    const isFilterActive = function () {
                        return (filterState?.value || '') !== '' || Boolean(filterVerified?.checked);
                    };

                    const setCardFilterHidden = function (card, hidden) {
                        if (hidden) {
                            card.setAttribute('data-shop-filter-hidden', '');
                        } else {
                            card.removeAttribute('data-shop-filter-hidden');
                        }
                    };

                    const shopMatchesFilters = function (card) {
                        const selectedStateId = filterState?.value || '';
                        const verifiedOnly = Boolean(filterVerified?.checked);
                        const cardStateId = String(card.getAttribute('data-shop-state-id') ?? '');
                        const cardVerified = card.getAttribute('data-shop-verified') === '1';

                        if (selectedStateId !== '' && cardStateId !== selectedStateId) {
                            return false;
                        }

                        if (verifiedOnly && !cardVerified) {
                            return false;
                        }

                        return true;
                    };

                    const setFilterMenuOpen = function (isOpen) {
                        if (!filterMenu || !filterToggle) {
                            return;
                        }

                        filterMenu.classList.toggle('hidden', !isOpen);
                        filterToggle.setAttribute('aria-expanded', String(isOpen));
                    };

                    const resetLazyLoadVisibility = function () {
                        shopCards.forEach(function (card, index) {
                            const hideForLazy = mobileMedia.matches && index >= initialBatchSize;
                            card.classList.toggle('hidden', hideForLazy);
                        });

                        if (loadMoreWrap) {
                            loadMoreWrap.classList.toggle(
                                'hidden',
                                shopCards.length <= initialBatchSize || !mobileMedia.matches,
                            );
                        }

                        if (signupCard) {
                            signupCard.classList.toggle(
                                'hidden',
                                shopCards.length > initialBatchSize && mobileMedia.matches,
                            );
                        }

                        filterEmpty?.classList.add('hidden');
                    };

                    const applyShopFilters = function () {
                        if (!shopsList) {
                            return;
                        }

                        if (!isFilterActive()) {
                            shopCards.forEach(function (card) {
                                setCardFilterHidden(card, false);
                            });
                            resetLazyLoadVisibility();

                            return;
                        }

                        let visibleCount = 0;

                        shopCards.forEach(function (card) {
                            const match = shopMatchesFilters(card);

                            setCardFilterHidden(card, !match);

                            if (match) {
                                card.classList.remove('hidden');
                                visibleCount++;
                            }
                        });

                        loadMoreWrap?.classList.add('hidden');
                        signupCard?.classList.remove('hidden');
                        filterEmpty?.classList.toggle('hidden', visibleCount > 0);
                    };

                    filterToggle?.addEventListener('click', function (event) {
                        event.stopPropagation();

                        if (!filterMenu) {
                            return;
                        }

                        setFilterMenuOpen(filterMenu.classList.contains('hidden'));
                    });

                    document.addEventListener('click', function (event) {
                        if (!filterRoot || filterMenu?.classList.contains('hidden')) {
                            return;
                        }

                        if (!filterRoot.contains(event.target)) {
                            setFilterMenuOpen(false);
                        }
                    });

                    filterState?.addEventListener('change', applyShopFilters);
                    filterVerified?.addEventListener('change', applyShopFilters);

                    const inlineLink = document.querySelector('[data-shops-jump]');
                    const fixedBar = document.querySelector('[data-shops-jump-fixed]');
                    const fixedLink = document.querySelector('[data-shops-jump-fixed-link]');
                    const jumpAnchor = document.querySelector('[data-shops-jump-anchor]');
                    const target = document.getElementById('shops');

                    if (!inlineLink || !fixedBar || !fixedLink || !target || !jumpAnchor) {
                        return;
                    }

                    const headerOffset = 80;
                    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    let shopsLazyInitialized = false;

                    const hideFixedBar = function () {
                        fixedBar.classList.add('opacity-0', 'pointer-events-none');
                        fixedBar.setAttribute('aria-hidden', 'true');
                    };

                    const showFixedBar = function () {
                        fixedBar.classList.remove('opacity-0', 'pointer-events-none');
                        fixedBar.setAttribute('aria-hidden', 'false');
                    };

                    const setJumpVisibility = function () {
                        if (!mobileMedia.matches) {
                            hideFixedBar();

                            return;
                        }

                        const anchorRect = jumpAnchor.getBoundingClientRect();
                        const rect = target.getBoundingClientRect();
                        const middleBandTop = window.innerHeight * 0.3;
                        const middleBandBottom = window.innerHeight * 0.65;
                        const isOriginalButtonAboveViewport = anchorRect.bottom < 0;
                        const isShopsInMiddle = rect.top <= middleBandBottom && rect.bottom >= middleBandTop;
                        const shouldShowFixed = isOriginalButtonAboveViewport && !isShopsInMiddle;

                        if (shouldShowFixed) {
                            showFixedBar();
                        } else {
                            hideFixedBar();
                        }
                    };

                    const scrollToShops = function (event) {
                        event.preventDefault();

                        const activeLink = event.currentTarget;
                        activeLink.classList.add('is-scrolling');

                        const top = target.getBoundingClientRect().top + window.scrollY - headerOffset;

                        const finish = function () {
                            activeLink.classList.remove('is-scrolling');
                            target.classList.add('ps-shops-section--highlight');
                            window.setTimeout(function () {
                                target.classList.remove('ps-shops-section--highlight');
                            }, 900);
                        };

                        if (reducedMotion) {
                            window.scrollTo(0, top);
                            finish();

                            return;
                        }

                        window.scrollTo({ top: top, behavior: 'smooth' });

                        if ('onscrollend' in window) {
                            window.addEventListener('scrollend', finish, { once: true });
                        } else {
                            window.setTimeout(finish, 750);
                        }
                    };

                    inlineLink.addEventListener('click', scrollToShops);
                    fixedLink.addEventListener('click', scrollToShops);

                    setJumpVisibility();
                    window.addEventListener('scroll', setJumpVisibility, { passive: true });
                    window.addEventListener('resize', setJumpVisibility);
                    mobileMedia.addEventListener?.('change', function () {
                        setJumpVisibility();

                        if (!isFilterActive()) {
                            resetLazyLoadVisibility();
                        } else {
                            applyShopFilters();
                        }
                    });

                    const initializeMobileLazyLoading = function () {
                        if (shopsLazyInitialized || !mobileMedia.matches || !shopsList || !loadMore || isFilterActive()) {
                            return;
                        }

                        shopsLazyInitialized = true;
                        const cards = Array.from(shopsList.querySelectorAll('[data-shop-card]'));
                        const batchSize = Number(shopsList.dataset.batchSize || 10);
                        let visibleCount = cards.filter((card) => !card.classList.contains('hidden')).length;

                        const revealNextBatch = function () {
                            const nextCount = Math.min(visibleCount + batchSize, cards.length);

                            cards.slice(visibleCount, nextCount).forEach(function (card) {
                                card.classList.remove('hidden');
                            });

                            visibleCount = nextCount;

                            if (visibleCount >= cards.length) {
                                loadMoreWrap?.classList.add('hidden');
                                signupCard?.classList.remove('hidden');
                            }
                        };

                        loadMore.addEventListener('click', revealNextBatch);

                        if ('IntersectionObserver' in window) {
                            const observer = new IntersectionObserver(function (entries) {
                                entries.forEach(function (entry) {
                                    if (entry.isIntersecting) {
                                        revealNextBatch();

                                        if (visibleCount >= cards.length) {
                                            observer.disconnect();
                                        }
                                    }
                                });
                            }, {
                                // Trigger only in the center band so the lazy loading is visible.
                                rootMargin: '-40% 0px -40% 0px',
                                threshold: 0,
                            });

                            observer.observe(loadMore);
                        }
                    };

                    if (!shopsList || !loadMore) {
                        return;
                    }

                    initializeMobileLazyLoading();
                    mobileMedia.addEventListener?.('change', initializeMobileLazyLoading);
                })();
            </script>
        @endpush
    @endif
@endsection
