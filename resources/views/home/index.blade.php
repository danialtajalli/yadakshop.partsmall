@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-10 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white to-white px-5 py-8 sm:px-8 sm:py-10">
            <x-ui.section-heading
                label="پلتفرم لوازم یدکی"
                :title="$title"
                description="فروشگاه‌ها، تعمیرگاه‌ها، نمایندگی‌ها و قطعات خودرو — همه در یکجا"
                heading="h1"
            />
        </div>
    </div>

    <x-home.vehicle-filter :vehicle-filter="$vehicleFilter" />

    <x-home.entity-grid
        title="فروشگاه‌های لوازم یدکی"
        description="فروشگاه‌های معتبر قطعات خودرو"
        :items="$shops"
        :more-url="route('shops.index')"
        more-label="مشاهده همه فروشگاه‌ها"
        profile-route="shop.profile"
        empty-message="هنوز فروشگاهی ثبت نشده است."
    />

    <x-home.stats-strip />

    <x-home.entity-carousel
        title="تعمیرگاه‌ها"
        description="تعمیرگاه‌های تخصصی خودرو"
        :items="$repairShops"
        :more-url="route('repair-shops.index')"
        more-label="مشاهده همه تعمیرگاه‌ها"
        profile-route="repair-shop.profile"
        empty-message="هنوز تعمیرگاهی ثبت نشده است."
    />

    <x-home.entity-carousel
        title="نمایندگی‌ها"
        description="نمایندگی‌های رسمی و خدمات پس از فروش"
        :items="$representations"
        :more-url="route('representations.index')"
        more-label="مشاهده همه نمایندگی‌ها"
        profile-route="representation.profile"
        empty-message="هنوز نمایندگی‌ای ثبت نشده است."
    />
@endsection

@push('full-bleed')
    <x-home.signup-banner />
@endpush

@section('content-tail')
    <section id="companies" class="scroll-mt-24 mb-12">
        <x-ui.section-heading
            class="mb-6"
            title="ماشین ها"
            description="برند ماشین خود را انتخاب کنید، تا به صفحه انتخاب قطعه هدایت شوید."
            :more-url="route('companies.index')"
            more-label="مشاهده همه برندها"
        />

        @if ($companies->isEmpty())
            <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
                <p class="text-sm text-ink-muted">برند ماشینی برای نمایش ثبت نشده است.</p>
            </div>
        @else
            <div
                class="mb-8"
                data-no-progress
                x-data="catalogClientSearch({
                    itemSelector: '.home-company-card',
                    textAttribute: 'companyName',
                    minChars: {{ \App\Support\CatalogSearch::MIN_CHARS }},
                    debounceMs: {{ \App\Support\CatalogSearch::CLIENT_DEBOUNCE_MS }},
                })"
            >
                <x-catalog.search-bar
                    id="home-company-search"
                    placeholder="جستجوی نام برند ماشین..."
                    empty-message="برند ماشینی با این نام یافت نشد."
                    alpine
                    class="mb-0"
                />

                <div id="home-companies-grid" class="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                    @foreach ($companies as $company)
                        @php
                            $companySlug = data_get($company, 'slug');
                            $companyName = data_get($company, 'name');
                            $companyHasModels = (bool) data_get($company, 'has_models');
                        @endphp
                        <button
                            type="button"
                            @if ($companyHasModels)
                                data-company-picker-trigger
                                data-company-slug="{{ $companySlug }}"
                            @endif
                            class="home-company-card ps-card-interactive flex w-full flex-col items-center p-2 text-center disabled:cursor-not-allowed disabled:opacity-60 sm:p-4"
                            data-company-name="{{ $companyName }}"
                            @disabled(! $companyHasModels)
                        >
                            <x-ui.company-logo
                                class="mb-2"
                                :name="$companyName"
                                :logo-url="data_get($company, 'logo_url')"
                                size="sm"
                            />
                            <h3 class="line-clamp-2 text-xs font-semibold leading-4 text-ink sm:text-sm sm:leading-5">{{ $companyName }}</h3>
                            @if (! $companyHasModels)
                                <p class="mt-1 text-[10px] leading-4 text-ink-muted sm:text-xs">خودرویی ثبت نشده است</p>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>

            <x-home.company-picker :company-picker="$companyPicker" />
        @endif
    </section>

    <x-home.part-category-cards :parts-by-name="$partCategoryParts" />

    <x-home.best-shops-banner :shops="$bestShops" />

    <section id="parts" class="scroll-mt-24">
        <x-ui.section-heading
            class="mb-6"
            title="قطعات خودرو"
            description="فهرست کامل قطعات — برای جزئیات و خودروهای مرتبط روی هر قطعه کلیک کنید"
            :more-url="route('car.parts')"
            more-label="مشاهده همه قطعات"
        />

        @if ($parts->isEmpty())
            <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
                <p class="text-sm text-ink-muted">قطعه‌ای برای نمایش ثبت نشده است.</p>
            </div>
        @else
            <div
                data-no-progress
                x-data="catalogClientSearch({
                    itemSelector: '.home-part-card',
                    textAttribute: 'partName',
                    gridSelector: '#home-parts-grid',
                    loadMoreWrapSelector: '#home-parts-load-more-wrap',
                    loadMoreButtonSelector: '#home-parts-load-more',
                    initialRows: 8,
                    minChars: {{ \App\Support\CatalogSearch::MIN_CHARS }},
                    debounceMs: {{ \App\Support\CatalogSearch::CLIENT_DEBOUNCE_MS }},
                })"
            >
                <x-catalog.search-bar
                    id="home-part-search"
                    placeholder="جستجوی نام قطعه..."
                    empty-message="قطعه‌ای با این نام یافت نشد."
                    alpine
                    class="mb-0"
                />

                <div id="home-parts-grid" class="mt-4 grid grid-cols-3 gap-2 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
                    @foreach ($parts as $part)
                        <x-ui.part-card
                            :part="$part"
                            :url="route('part.show', data_get($part, 'slug'))"
                            class="home-part-card flex-col gap-1.5 p-2 sm:flex-row sm:gap-2.5 sm:p-3 [&_h3]:whitespace-normal [&_h3]:text-xs [&_h3]:leading-4 sm:[&_h3]:truncate sm:[&_h3]:text-sm [&_p]:hidden sm:[&_p]:block [&_svg]:size-4 sm:[&_svg]:size-[1.125rem]"
                            data-part-name="{{ data_get($part, 'title') }}"
                        />
                    @endforeach
                </div>

                <div id="home-parts-load-more-wrap" class="mt-5 hidden flex-col items-center justify-center gap-2">
                    <i class="fa-solid fa-arrow-down animate-bounce text-sm text-brand" aria-hidden="true"></i>
                    <button
                        type="button"
                        id="home-parts-load-more"
                        class="ps-btn-secondary min-w-44 justify-center"
                    >
                        نمایش بیشتر
                    </button>
                </div>
            </div>
        @endif
    </section>
@endsection
