@extends('layouts.app')

@if (isset($_GET['page']) && $_GET['page'] > 1
 && Route::current()->getName() != 'shops.index')
@push('head')
<meta name="robots" content="noindex, follow" />
@endpush
@endif

@section('title', $title)

@section('content')
    @php
        $listingAction = route(match ($type) {
            'repair_shop' => 'repair-shops.index',
            'representation' => 'representations.index',
            default => 'shops.index',
        });
    @endphp

    <x-site.breadcrumb :items="$breadcrumbs" />

    <div class="mb-8 flex flex-col gap-6 md:flex-row md:items-start md:gap-8">
        <div class="min-w-0 flex-1">
            <div class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
                <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
                    <x-ui.section-heading
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

        <div data-catalog-search-results>
            <div class="mb-4 flex min-w-0 flex-wrap items-center justify-between gap-3">
                <p class="min-w-0 text-sm text-ink-muted">
                    {{ number_format($listings->total()) }} مورد یافت شد
                </p>
                <div class="flex min-w-0 flex-wrap gap-2 text-sm">
                    @php
                        $listingFilterQuery = array_filter([
                            'q' => $filters['q'] ?? null,
                            'state_id' => $filters['state_id'] ?? null,
                            'city_id' => $filters['city_id'] ?? null,
                            'specialization_id' => $filters['specialization_id'] ?? null,
                        ], fn ($value) => $value !== null && $value !== '');
                    @endphp
                    <a
                        href="{{ route('shops.index', $listingFilterQuery) }}"
                        @class([
                            'rounded-lg px-3 py-1.5 transition break-words',
                            'bg-brand text-white' => $type === 'shop',
                            'text-ink-muted hover:bg-surface hover:text-ink' => $type !== 'shop',
                        ])
                    >
                        فروشگاه‌ها
                    </a>
                    <a
                        href="{{ route('repair-shops.index', $listingFilterQuery) }}"
                        @class([
                            'rounded-lg px-3 py-1.5 transition break-words',
                            'bg-brand text-white' => $type === 'repair_shop',
                            'text-ink-muted hover:bg-surface hover:text-ink' => $type !== 'repair_shop',
                        ])
                    >
                        تعمیرگاه‌ها
                    </a>
                    <a
                        href="{{ route('representations.index', $listingFilterQuery) }}"
                        @class([
                            'rounded-lg px-3 py-1.5 transition break-words',
                            'bg-brand text-white' => $type === 'representation',
                            'text-ink-muted hover:bg-surface hover:text-ink' => $type !== 'representation',
                        ])
                    >
                        نمایندگی‌ها
                    </a>
                </div>
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
@endsection
