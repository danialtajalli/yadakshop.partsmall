@extends('layouts.app')

@section('hide_floating_call')
@endsection

@section('title', $title)

@section('content')
    @php
        $websiteLink = $shop->website_show;
        $websiteUrl = $websiteLink ? $websiteLink->link_type->actionUrl($websiteLink->name) : null;
        $websiteLabel = $websiteUrl
            ? trim((string) preg_replace('#^https?://#i', '', $websiteUrl), '/')
            : null;
        $shareUrl = route('shop.profile', $shop->slug);
        $qrImageUrl = \App\Support\ShopImageUrlBuilder::shopQrCodeUrl($shop->slug);
        $locationLabel = collect([$shop->city?->name, $shop->city?->state?->name])->filter()->implode('، ');
        $hasMap = $shop->latitude && $shop->longitude;
        $pageViewCount = (int) ($shop->visited_count ?? 0);
        $qrDialogId = 'shop-qr-gallery-'.md5($shareUrl);
        $shopHeadingMetaItems = collect([
            ['key' => 'rating', 'visible' => (bool) $averageRating],
            ['key' => 'comments', 'visible' => $commentsCount > 0],
            ['key' => 'location', 'visible' => (bool) $locationLabel],
            ['key' => 'website', 'visible' => (bool) $websiteUrl],
        ])->filter(fn (array $item): bool => $item['visible'])->values();
    @endphp

    <x-site.breadcrumb :items="[
        ['label' => 'خانه', 'url' => url('/')],
        ['label' => 'فروشگاه‌ها', 'url' => route('shops.index')],
        ['label' => $shop->name, 'active' => true],
    ]" />

    <header class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        @if ($shop->cover ?? null)
            <div class="relative h-36 w-full overflow-hidden sm:h-44">
                <img src="{{ $shop->cover }}" alt="تصویر فروشگاه {{ $shop->name }}" class="size-full object-cover">
                <div class="pointer-events-none absolute inset-0 bg-gradient-to-t from-ink/25 via-transparent to-transparent" aria-hidden="true"></div>
            </div>
        @endif

        <div class="border-b border-line bg-gradient-to-l from-surface via-white to-white px-4 py-5 sm:px-6 sm:py-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between lg:gap-8">
                <div class="flex min-w-0 flex-1 flex-col gap-4 sm:flex-row sm:items-start sm:gap-5">
                    <x-ui.company-logo
                        :name="$shop->name"
                        :logo-url="$shop->logo ?? null"
                        alt="لوگوی فروشگاه {{ $shop->name }}"
                        size="xl"
                        @class([
                            'shrink-0',
                            'ring-1 ring-line' => ! filled($shop->cover ?? null),
                            'relative z-[1] -mt-12 shadow-card ring-2 ring-white sm:-mt-14' => filled($shop->cover ?? null),
                        ])
                    />

                    <div class="min-w-0 flex-1 space-y-3 sm:pt-0.5">
                        <div class="space-y-1.5">
                            <h1 class="text-balance text-2xl font-bold leading-tight tracking-tight text-ink sm:text-3xl">
                                {{ $shop->name }}
                            </h1>

                            @if ($shop->secondary_name)
                                <p class="text-sm leading-6 text-ink-muted sm:text-[15px]">{{ $shop->secondary_name }}</p>
                            @endif
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <x-shop.open-status :open="$isOpen" variant="compact" />

                            @if ($shop->verified ?? false)
                                <x-shop.trusted-badge />
                            @endif
                        </div>

                        @if ($shopHeadingMetaItems->isNotEmpty())
                            <ul class="flex flex-wrap items-center gap-x-3 gap-y-2 text-sm text-ink-muted" role="list">
                                @foreach ($shopHeadingMetaItems as $index => $metaItem)
                                    @if ($index > 0)
                                        <li class="h-4 w-px self-center bg-line" aria-hidden="true"></li>
                                    @endif

                                    @switch($metaItem['key'])
                                        @case('rating')
                                            <li class="inline-flex items-center gap-1.5 font-semibold text-accent">
                                                <svg class="size-3.5 fill-current" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z"/></svg>
                                                <span class="tabular-nums">{{ number_format($averageRating, 1) }}</span>
                                                <span class="sr-only">از ۵</span>
                                            </li>
                                            @break

                                        @case('comments')
                                            <li class="inline-flex items-center gap-1.5">
                                                <i class="fa-regular fa-comment text-[12px] text-brand" aria-hidden="true"></i>
                                                <span class="tabular-nums">{{ number_format($commentsCount) }} نظر</span>
                                            </li>
                                            @break

                                        @case('location')
                                            <li class="inline-flex min-w-0 items-center gap-1.5">
                                                <i class="fa-solid fa-location-dot text-[12px] text-brand" aria-hidden="true"></i>
                                                @if ($hasMap)
                                                    <a href="#shop-location" class="truncate transition hover:text-ink">{{ $locationLabel }}</a>
                                                @else
                                                    <span class="truncate">{{ $locationLabel }}</span>
                                                @endif
                                            </li>
                                            @break

                                        @case('website')
                                            <li class="inline-flex min-w-0 items-center gap-1.5">
                                                <i class="fa-solid fa-globe text-[12px] text-[#2563eb]" aria-hidden="true"></i>
                                                <a
                                                    href="{{ $websiteUrl }}"
                                                    target="_blank"
                                                    rel="noopener sponsored nofollow"
                                                    class="truncate font-medium text-ink transition hover:text-brand"
                                                    dir="ltr"
                                                    title="{{ $websiteLabel }}"
                                                >{{ $websiteLabel }}</a>
                                            </li>
                                            @break
                                    @endswitch
                                @endforeach
                            </ul>
                        @endif

                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                            <div class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-line bg-white px-2.5 text-xs text-ink-muted">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                <span class="tabular-nums font-semibold text-ink">{{ number_format($pageViewCount) }}</span>
                                <span>بازدید</span>
                            </div>

                            <button
                                type="button"
                                class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-line bg-white px-2.5 text-xs font-medium text-ink-muted transition hover:border-brand/30 hover:bg-brand-soft/40 hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/30 lg:hidden"
                                data-ps-qr-open="{{ $qrDialogId }}"
                                aria-haspopup="dialog"
                                aria-controls="{{ $qrDialogId }}"
                                aria-label="مشاهده QR صفحه"
                            >
                                <i class="fa-solid fa-qrcode text-[12px]" aria-hidden="true"></i>
                                <span>QR صفحه</span>
                            </button>

                            <button
                                type="button"
                                data-shop-share
                                data-shop-share-target="shop-share-sheet"
                                data-share-url="{{ $shareUrl }}"
                                data-share-title="{{ $shop->name }}"
                                class="inline-flex h-8 items-center gap-1.5 rounded-lg border border-line bg-white px-2.5 text-xs font-medium text-ink-muted transition hover:border-brand/30 hover:bg-brand-soft/40 hover:text-ink focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/30 lg:hidden"
                                aria-haspopup="dialog"
                                aria-controls="shop-share-sheet"
                            >
                                <i class="fa-solid fa-share-nodes text-[12px]" aria-hidden="true"></i>
                                <span data-shop-share-label>اشتراک‌گذاری</span>
                            </button>
                        </div>
                    </div>
                </div>

                <aside class="hidden shrink-0 lg:block lg:w-[9.5rem]">
                    <p class="mb-2 text-xs font-medium text-ink-muted lg:text-center">QR صفحه فروشگاه</p>
                    <x-shop.qr-gallery
                        variant="hero"
                        :url="$shareUrl"
                        :image-url="$qrImageUrl"
                        :title="$shop->name"
                        :dialog-id="$qrDialogId"
                        class="mx-auto w-full"
                    />
                    <button
                        type="button"
                        data-shop-share
                        data-shop-share-target="shop-share-sheet"
                        data-share-url="{{ $shareUrl }}"
                        data-share-title="{{ $shop->name }}"
                        class="mx-auto mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg border border-line bg-white px-3 py-1.5 text-xs font-medium text-ink transition hover:border-brand/30 hover:bg-brand-soft/40 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/30"
                        aria-haspopup="dialog"
                        aria-controls="shop-share-sheet"
                    >
                        <i class="fa-solid fa-share-nodes text-[12px]" aria-hidden="true"></i>
                        <span data-shop-share-label>اشتراک‌گذاری</span>
                    </button>
                </aside>
            </div>
        </div>
    </header>

    <div class="mb-3 grid gap-8 lg:grid-cols-12">
        <aside class="order-1 space-y-6 lg:order-2 lg:col-span-4">
            @if ($shop->phones->isNotEmpty())
                <section id="shop-phones" class="ps-card ps-shop-phones-section scroll-mt-20 p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">تماس</h2>
                    <x-ui.phone-icons :phones="$shop->phones" colorful-branded />
                </section>
            @endif

            @if ($shop->person_responsible_name || $shop->person_responsible_email)
                <section class="ps-card p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">مسئول فروشگاه</h2>
                    <dl class="space-y-3 text-sm">
                        @if ($shop->person_responsible_name)
                            <div>
                                <dt class="text-ink-muted">نام</dt>
                                <dd class="mt-0.5 font-medium text-ink">{{ $shop->person_responsible_name }}</dd>
                            </div>
                        @endif
                        @if ($shop->person_responsible_email)
                            <div>
                                <dt class="text-ink-muted">ایمیل</dt>
                                <dd class="mt-0.5">
                                    <a href="mailto:{{ $shop->person_responsible_email }}" class="font-medium text-brand hover:text-brand-dark" dir="ltr">
                                        {{ $shop->person_responsible_email }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </section>
            @endif

            @if ($shop->links->isNotEmpty() || $websiteLink)
                <section class="ps-card p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">شبکه‌های اجتماعی و وب‌سایت</h2>
                    <x-ui.social-icons
                        :links="collect($websiteLink ? [$websiteLink] : [])->merge($shop->links)"
                        colorful-branded
                    />
                </section>
            @endif

            <section class="ps-card p-5">
                <h2 class="mb-4 text-base font-bold text-ink">ساعات کاری</h2>

                <x-shop.open-status :open="$isOpen" />

                @php
                    use App\Support\PersianDigits;

                    $formatShopTime = static function (?string $time): ?string {
                        if ($time === null || $time === '') {
                            return null;
                        }

                        $normalized = preg_match('/^\d{1,2}:\d{2}/', $time, $matches) ? $matches[0] : $time;

                        return PersianDigits::convert($normalized);
                    };

                    $weekday = now()->dayOfWeek;
                    $todayKey = match ($weekday) {
                        4 => 'thursday',
                        5 => 'friday',
                        default => 'weekday',
                    };

                    $scheduleRows = collect([
                        [
                            'key' => 'weekday',
                            'label' => 'شنبه تا چهارشنبه',
                            'open' => $formatShopTime($shop->open_time),
                            'close' => $formatShopTime($shop->close_time),
                        ],
                        [
                            'key' => 'thursday',
                            'label' => 'پنج‌شنبه',
                            'open' => $formatShopTime($shop->open_time_thursday),
                            'close' => $formatShopTime($shop->close_time_thursday),
                        ],
                        [
                            'key' => 'friday',
                            'label' => 'جمعه',
                            'open' => $formatShopTime($shop->open_time_friday),
                            'close' => $formatShopTime($shop->close_time_friday),
                        ],
                    ])->filter(fn (array $row): bool => filled($row['open']) && filled($row['close']));
                @endphp

                @if ($scheduleRows->isNotEmpty())
                    <ul class="mt-1 space-y-2" role="list">
                        @foreach ($scheduleRows as $row)
                            @php
                                $isToday = $row['key'] === $todayKey;
                            @endphp
                            <li
                                @class([
                                    'flex items-center justify-between gap-3 rounded-xl border px-3 py-2.5 transition',
                                    'border-line bg-white shadow-card' => $isToday,
                                    'border-line/70 bg-surface/50' => ! $isToday,
                                ])
                            >
                                <div class="min-w-0 flex items-center gap-2.5">
                                    <span
                                        class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white text-ink-muted ring-1 ring-line"
                                        aria-hidden="true"
                                    >
                                        <i class="fa-regular fa-clock text-[12px]"></i>
                                    </span>
                                    <div class="min-w-0">
                                        <div class="flex min-w-0 flex-wrap items-center gap-2">
                                            <p class="truncate text-sm font-semibold text-ink">{{ $row['label'] }}</p>
                                            @if ($isToday)
                                                <span class="inline-flex shrink-0 items-center rounded-md border border-line bg-surface px-1.5 py-0.5 text-[10px] font-medium text-ink-muted">
                                                    امروز
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <span
                                    @class([
                                        'shrink-0 rounded-lg px-2.5 py-1 text-xs font-semibold tabular-nums tracking-wide text-ink',
                                        'border border-line bg-surface/80' => $isToday,
                                        'border border-transparent bg-white/60' => ! $isToday,
                                    ])
                                    dir="ltr"
                                >
                                    {{ $row['open'] }} – {{ $row['close'] }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-1 text-sm text-ink-muted">ساعات کاری ثبت نشده است.</p>
                @endif
            </section>

            <x-shop.related-shops
                :shops="$relatedShops"
                :companies="$shop->companies"
            />
        </aside>

        <div class="order-2 space-y-8 lg:order-1 lg:col-span-8">
            @if ($shop->description)
                <section class="ps-card px-5 py-6 sm:px-6">
                    <x-ui.section-heading title="درباره فروشگاه" />
                    <x-ui.expandable-description id="shop-description">
                        {!! $shop->description !!}
                    </x-ui.expandable-description>
                </section>
            @endif

            @if ($shop->companies->isNotEmpty())
                <section>
                    <x-ui.section-heading
                        title="برندها و شرکت‌های مرتبط"
                        description="خودروسازان و برندهایی که این فروشگاه پوشش می‌دهد"
                    />

                    <div class="grid grid-cols-2 gap-4 @if($shop->companies->count() <= 2) sm:grid-cols-2 @else sm:grid-cols-4 @endif">
                        @foreach ($shop->companies as $company)
                        <a href="{{ route('cars.index', $company->slug) }}">
                            <article class="ps-card flex items-center gap-4 p-4 sm:p-2">
                                <x-ui.company-logo
                                    :name="$company->name"
                                    :logo-url="$company->logo_url ?? null"
                                    alt="لوگوی برند خودرو {{ $company->name }}"
                                    size="listing"
                                />
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-ink">{{ $company->name }}</h3>
                                    @if ($company->country)
                                        <p class="mt-0.5 text-sm text-ink-muted">{{ $company->country }}</p>
                                    @endif
                                </div>
                            </article>
                        </a>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($shop->partsCategories->isNotEmpty())
                <section>
                    <x-ui.section-heading title="دسته‌بندی قطعات" />
                    <ul class="flex flex-wrap gap-2">
                        @foreach ($shop->partsCategories as $category)
                            <li class="rounded-xl bg-gray-100 px-3 py-1.5 text-sm font-medium text-brand-dark">
                                {{ $category->name }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($hasMap)
                <div id="shop-location" class="scroll-mt-24">
                    <x-ui.location-map
                        :latitude="$shop->latitude"
                        :longitude="$shop->longitude"
                        :title="$shop->name"
                        :address="$shop->address"
                    />
                </div>
            @endif

            <div class="hidden sm:block">
                <x-shop.comments-section
                    :shop="$shop"
                    :comments="$shop->comments"
                    :comments-count="$commentsCount"
                    :average-rating="$averageRating"
                    id-suffix="desktop"
                />
            </div>
        </div>
    </div>

    <div class="mt-8 space-y-8 sm:hidden">
        <x-shop.comments-section
            :shop="$shop"
            :comments="$shop->comments"
            :comments-count="$commentsCount"
            :average-rating="$averageRating"
            id-suffix="mobile"
        />
    </div>

    <x-shop.qr-gallery
        variant="dialog"
        :url="$shareUrl"
        :image-url="$qrImageUrl"
        :title="$shop->name"
        :dialog-id="$qrDialogId"
    />

    <x-shop.share-sheet
        :url="$shareUrl"
        :title="$shop->name"
        dialog-id="shop-share-sheet"
    />

    <x-shop.comment-success-modal />

    <x-ui.toast-host />

    @if ($shop->phones->isNotEmpty())
        @push('overlays')
            <button
                type="button"
                data-shop-phones-jump
                class="ps-shop-phones-jump fixed bottom-5 start-5 z-50 flex size-14 items-center justify-center rounded-full bg-brand text-white shadow-lg ring-1 ring-black/5 transition hover:bg-brand-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-brand/40 active:scale-95"
                aria-label="رفتن به شماره تماس"
            >
                <i class="fa-solid fa-phone text-lg" aria-hidden="true"></i>
            </button>
        @endpush

        @push('scripts')
            <script>
                (function () {
                    const jumpButton = document.querySelector('[data-shop-phones-jump]');
                    const phonesSection = document.getElementById('shop-phones');

                    if (!jumpButton || !phonesSection) {
                        return;
                    }

                    const headerOffset = 80;
                    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    jumpButton.addEventListener('click', function (event) {
                        event.preventDefault();
                        jumpButton.classList.add('is-scrolling');

                        const top = phonesSection.getBoundingClientRect().top + window.scrollY - headerOffset;

                        const finish = function () {
                            jumpButton.classList.remove('is-scrolling');
                            phonesSection.classList.add('ps-shop-phones-section--highlight');
                            window.setTimeout(function () {
                                phonesSection.classList.remove('ps-shop-phones-section--highlight');
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
                    });
                })();
            </script>
        @endpush
    @endif
@endsection
