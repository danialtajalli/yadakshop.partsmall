@extends('layouts.app')

@if (isset($_GET['page']) && $_GET['page'] > 1
 && ! Route::current()->named('shops.index', 'shops.company'))
@push('head')
<meta name="robots" content="noindex, follow" />
@endpush
@endif

@section('title', $title)

@section('content')
    @php
        $listingAction = match ($type) {
            'repair_shop' => route('repair-shops.index'),
            'representation' => route('representations.index'),
            default => ($filterCompany ?? null)
                ? route('shops.company', $filterCompany)
                : route('shops.index'),
        };
    @endphp

    <x-site.breadcrumb :items="$breadcrumbs" />

    @if ($type === 'shop')
        <section class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
            <div class="relative border-b border-line bg-gradient-to-l from-[#fff4eb] via-white to-white px-5 py-8 sm:px-10 sm:py-10">
                <div class="pointer-events-none absolute -end-10 -top-16 size-48 rounded-full bg-brand-soft/15 blur-3xl" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -bottom-20 start-1/3 size-40 rounded-full bg-brand/5 blur-2xl" aria-hidden="true"></div>

                @if (isset($filterCompany) && $filterCompany && filled($filterCompany->logo_url ?? null))
                    <div
                        class="pointer-events-none absolute inset-y-0 end-0 w-[55%] max-w-md sm:w-[45%]"
                        aria-hidden="true"
                    >
                        <img
                            src="{{ $filterCompany->logo_url }}"
                            alt=""
                            class="absolute end-[-10%] top-1/2 h-[140%] w-auto max-w-none -translate-y-1/2 object-contain opacity-[0.12] sm:opacity-[0.14]"
                            loading="lazy"
                            decoding="async"
                        >
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/40 to-white"></div>
                    </div>
                @endif

                <div class="relative min-w-0">
                    <p class="ps-section-label">فروشگاه‌ها</p>
                    <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
                        <div class="min-w-0 max-w-3xl">
                            <h1 class="text-3xl font-black tracking-tight text-ink sm:text-4xl lg:text-[2.75rem] lg:leading-tight">
                                {{ $title }}
                            </h1>
                            <p class="mt-3 max-w-2xl text-sm leading-7 text-ink-muted sm:text-base">
                                @if (isset($filterCompany) && $filterCompany)
                                    فروشگاه‌های مرتبط با {{ $filterCompany->name }} — جستجو بر اساس استان و شهر
                                @else
                                    جستجو و فیلتر فروشگاه‌های لوازم یدکی بر اساس استان، شهر و برند
                                @endif
                            </p>
                        </div>

                        <div class="shrink-0 rounded-2xl border border-line bg-white/80 px-4 py-3 text-center shadow-sm backdrop-blur-sm">
                            <p class="text-[11px] font-medium text-ink-muted">تعداد فروشگاه‌ها</p>
                            <p class="mt-0.5 text-2xl font-black tabular-nums text-ink">{{ \App\Support\PersianDigits::convert(number_format($listings->total())) }}</p>
                        </div>
                    </div>

                    <x-listings.company-filter
                        :companies="$shopCompanies ?? collect()"
                        :selected="$filterCompany ?? null"
                    />
                </div>
            </div>
        </section>
    @else
        <div class="mb-8 flex flex-col gap-6 md:flex-row md:items-start md:gap-8">
            <div class="min-w-0 flex-1">
                <div class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
                    <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
                        <x-ui.section-heading
                            class="mb-0"
                            :label="match ($type) {
                                'repair_shop' => 'تعمیرگاه‌ها',
                                'representation' => 'نمایندگی‌ها',
                                default => 'فروشگاه‌ها',
                            }"
                            :title="$title"
                            :description="match ($type) {
                                'repair_shop' => 'جستجو و فیلتر تعمیرگاه‌ها بر اساس استان، شهر و تخصص',
                                'representation' => 'جستجو و فیلتر نمایندگی‌های رسمی بر اساس استان، شهر و برند',
                                default => 'جستجو و فیلتر فروشگاه‌های لوازم یدکی بر اساس استان و شهر',
                            }"
                            heading="h1"
                        />
                    </div>
                </div>
            </div>

            <x-site.sticky-cta-sidebar />
        </div>
    @endif

    <div
        data-no-progress
        x-data="catalogRemoteSearch({
            action: @js($listingAction),
            initialQuery: @js($filters['q'] ?? ''),
            initialStateId: @js($filters['state_id'] ?? ''),
            initialCityId: @js($filters['city_id'] ?? ''),
            initialSpecializationId: @js($filters['specialization_id'] ?? ''),
            csrf: @js(csrf_token()),
            minChars: {{ \App\Support\CatalogSearch::MIN_CHARS }},
            debounceMs: {{ \App\Support\CatalogSearch::DEBOUNCE_MS }},
        })"
    >
        <x-listings.filters
            :action="$listingAction"
            :filters="$filters"
            :states="$states"
            :cities="$cities"
            :cities-by-state="$citiesByState"
            :specializations="$specializations"
            :show-specialization-filter="$showSpecializationFilter"
        />

        <div
            class="relative ps-shops-section"
            :class="{ 'is-filter-loading': loading }"
        >
            <div
                class="ps-shops-filter-loading"
                aria-hidden="true"
                x-bind:aria-hidden="!loading"
            >
                <svg class="ps-shops-filter-spinner size-9 text-brand" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"></circle>
                    <path d="M12 2a10 10 0 0 1 10 10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                </svg>
                <span class="sr-only">در حال اعمال فیلتر</span>
            </div>

            <div class="ps-shops-section__content" data-catalog-search-results>
                <div class="mb-4">
                    <p class="min-w-0 text-sm text-ink-muted">
                        {{ number_format($listings->total()) }} مورد یافت شد
                    </p>
                </div>

                @if ($listings->isEmpty())
                    <x-catalog.search-empty
                        message="موردی با این فیلترها یافت نشد."
                        alpine
                        boxed
                    />
                @else
                    <h2 class="sr-only">نتایج جستجو</h2>
                    <div class="grid min-w-0 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($listings as $listing)
                            <x-listings.card :listing="$listing" :type="$type" />
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $listings->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
