@extends('layouts.app')

@section('hide_floating_call')
@endsection

@section('title', $title)

@section('content')
    @php
        $websiteLink = $shop->website_show;
        $websiteUrl = $websiteLink ? $websiteLink->link_type->actionUrl($websiteLink->name) : null;
        $websiteLabel = $websiteLink?->name;
        $shareUrl = route('shop.profile', $shop->slug);
        $locationLabel = collect([$shop->city?->name, $shop->city?->state?->name])->filter()->implode('، ');
        $hasMap = $shop->latitude && $shop->longitude;
        $pageViewCount = 2000 + (abs(crc32($shop->slug)) % 1500) + 1;
        $qrDialogId = 'shop-qr-gallery-'.md5($shareUrl);
    @endphp

    <x-site.breadcrumb :items="[
        ['label' => 'خانه', 'url' => url('/')],
        ['label' => 'فروشگاه‌ها', 'url' => route('shops.index')],
        ['label' => $shop->name, 'active' => true],
    ]" />

    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        @if ($shop->cover ?? null)
            <div class="h-40 w-full overflow-hidden border-b border-line sm:h-52">
                <img src="{{ $shop->cover }}" alt="" class="size-full object-cover">
            </div>
        @endif

        <div class="bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <div class="flex gap-4 sm:gap-5">
                <x-ui.company-logo
                    :name="$shop->name"
                    :logo-url="$shop->logo ?? null"
                    size="xl"
                    class="shrink-0"
                />

                <div class="min-w-0 flex-1 text-start">
                    <div class="flex min-w-0 flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $shop->name }}</h1>
                        @if ($shop->verified ?? false)
                            <x-shop.trusted-badge />
                        @endif
                    </div>

                    @if ($shop->secondary_name)
                        <p class="mt-1 text-sm text-ink-muted">{{ $shop->secondary_name }}</p>
                    @endif

                    <div class="mt-2.5 flex flex-wrap items-center gap-x-3 gap-y-1.5 text-xs text-ink-muted sm:text-sm">
                        @if ($averageRating)
                            <span class="inline-flex items-center gap-1 rounded-md bg-accent-soft px-2 py-0.5 font-medium text-accent">
                                <svg class="size-3.5 fill-current" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z"/></svg>
                                {{ number_format($averageRating, 1) }}
                            </span>
                        @endif

                        @if ($commentsCount > 0)
                            <span>{{ number_format($commentsCount) }} نظر</span>
                        @endif

                        <span class="inline-flex items-center gap-1">
                            <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            {{ number_format($pageViewCount) }} بازدید
                        </span>

                        @if ($locationLabel)
                            <span class="inline-flex items-center gap-1">
                                <i class="fa-solid fa-location-dot text-brand" aria-hidden="true"></i>
                                {{ $locationLabel }}
                            </span>
                        @endif
                    </div>

                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        @if ($hasMap)
                            <a
                                href="#shop-location"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-white px-2.5 py-1.5 text-[11px] font-medium text-ink-muted transition hover:border-brand/30 hover:text-ink"
                            >
                                <i class="fa-solid fa-map-location-dot text-brand text-xs" aria-hidden="true"></i>
                                نقشه
                            </a>
                        @endif

                        <x-shop.qr-gallery
                            variant="compact"
                            :url="$shareUrl"
                            :title="$shop->name"
                            :dialog-id="$qrDialogId"
                        />

                        @if ($websiteUrl)
                            <a
                                href="{{ $websiteUrl }}"
                                target="_blank"
                                rel="noopener"
                                class="inline-flex max-w-full items-center gap-1.5 rounded-lg border border-line bg-white px-2.5 py-1.5 text-[11px] font-medium text-ink transition hover:border-brand/30 hover:bg-brand-soft/40"
                            >
                                <i class="fa-solid fa-globe shrink-0 text-[#2563eb] text-xs" aria-hidden="true"></i>
                                <span class="truncate" dir="ltr">{{ $websiteLabel }}</span>
                            </a>
                        @endif

                        <button
                            type="button"
                            data-shop-share
                            data-share-url="{{ $shareUrl }}"
                            data-share-title="{{ $shop->name }}"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-line bg-white px-2.5 py-1.5 text-[11px] font-medium text-ink transition hover:border-brand/30 hover:bg-brand-soft/40"
                        >
                            <i class="fa-solid fa-share-nodes text-xs" aria-hidden="true"></i>
                            <span data-shop-share-label>اشتراک‌گذاری</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

            @if ($shop->links->isNotEmpty())
                <section class="ps-card p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">شبکه‌های اجتماعی</h2>
                    <x-ui.social-icons :links="$shop->links" colorful-branded />
                </section>
            @endif

            <section class="ps-card p-5">
                <h2 class="mb-4 text-base font-bold text-ink">QR صفحه</h2>
                <x-shop.qr-gallery
                    :url="$shareUrl"
                    :title="$shop->name"
                    :dialog-id="$qrDialogId"
                />
            </section>

            <section class="ps-card p-5">
                <h2 class="mb-4 text-base font-bold text-ink">ساعات کاری</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-ink-muted">شنبه تا چهارشنبه</dt>
                        <dd class="tabular-nums font-medium text-ink" dir="ltr">{{ $shop->open_time }} – {{ $shop->close_time }}</dd>
                    </div>
                    @if ($shop->open_time_thursday && $shop->close_time_thursday)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-ink-muted">پنج‌شنبه</dt>
                            <dd class="tabular-nums font-medium text-ink" dir="ltr">{{ $shop->open_time_thursday }} – {{ $shop->close_time_thursday }}</dd>
                        </div>
                    @endif
                    @if ($shop->open_time_friday && $shop->close_time_friday)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-ink-muted">جمعه</dt>
                            <dd class="tabular-nums font-medium text-ink" dir="ltr">{{ $shop->open_time_friday }} – {{ $shop->close_time_friday }}</dd>
                        </div>
                    @endif
                </dl>
            </section>
        </aside>

        <div class="order-2 space-y-8 lg:order-1 lg:col-span-8">
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

    <div class="sm:hidden sm:mt-4">
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
        :title="$shop->name"
        :dialog-id="$qrDialogId"
    />

    <x-ui.toast-host />

    @push('scripts')
        <script>
            (function () {
                const shareButton = document.querySelector('[data-shop-share]');

                if (!shareButton) {
                    return;
                }

                const shareUrl = shareButton.dataset.shareUrl || window.location.href;

                shareButton.addEventListener('click', function () {
                    window.psCopyAndToast?.(shareUrl, 'لینک صفحه کپی شد', 'کپی لینک انجام نشد');
                });
            })();
        </script>
    @endpush

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
