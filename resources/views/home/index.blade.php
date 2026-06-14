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

    <x-home.entity-carousel
        title="فروشگاه‌های لوازم یدکی"
        description="فروشگاه‌های معتبر قطعات خودرو"
        :items="$shops"
        :more-url="route('shops.index')"
        profile-route="shop.profile"
        empty-message="هنوز فروشگاهی ثبت نشده است."
    />

    <x-home.entity-carousel
        title="تعمیرگاه‌ها"
        description="تعمیرگاه‌های تخصصی خودرو"
        :items="$repairShops"
        :more-url="route('repair-shops.index')"
        profile-route="repair-shop.profile"
        empty-message="هنوز تعمیرگاهی ثبت نشده است."
    />

    <x-home.entity-carousel
        title="نمایندگی‌ها"
        description="نمایندگی‌های رسمی و خدمات پس از فروش"
        :items="$representations"
        :more-url="route('representations.index')"
        profile-route="representation.profile"
        empty-message="هنوز نمایندگی‌ای ثبت نشده است."
    />

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
            <div class="mb-8">
                <label for="home-company-search" class="sr-only">جستجوی برند ماشین</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input
                        id="home-company-search"
                        type="search"
                        placeholder="جستجوی نام برند ماشین..."
                        autocomplete="off"
                        class="w-full rounded-2xl border border-line bg-white py-3.5 pe-4 ps-12 text-sm text-ink shadow-card outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
                    >
                </div>
                <p id="home-company-search-empty" class="mt-3 hidden text-sm text-ink-muted">برند ماشینی با این نام یافت نشد.</p>
            </div>

            <div id="home-companies-grid" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($companies as $company)
                    <button
                        type="button"
                        @if ($company->cars->flatMap->models->isNotEmpty())
                            data-company-picker-trigger
                            data-company-slug="{{ $company->slug }}"
                        @endif
                        class="home-company-card ps-card-interactive flex w-full flex-col p-5 text-start disabled:cursor-not-allowed disabled:opacity-60"
                        data-company-name="{{ $company->name }}"
                        @disabled($company->cars->flatMap->models->isEmpty())
                    >
                        <div class="mb-3 flex size-11 items-center justify-center overflow-hidden rounded-xl bg-brand-soft text-brand">
                            @if ($company->logo_url)
                                <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="size-full object-cover">
                            @else
                                {{ mb_substr($company->name, 0, 1) }}
                            @endif
                        </div>
                        <h3 class="text-base font-semibold text-ink">{{ $company->name }}</h3>
                        @if ($company->cars->flatMap->models->isEmpty())
                            <p class="mt-1 text-xs text-ink-muted">خودرویی ثبت نشده است</p>
                        @endif
                    </button>
                @endforeach
            </div>

            <x-home.company-picker :company-picker="$companyPicker" />
        @endif
    </section>

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
            <div class="mb-8">
                <label for="home-part-search" class="sr-only">جستجوی قطعه</label>
                <div class="relative">
                    <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input
                        id="home-part-search"
                        type="search"
                        placeholder="جستجوی نام قطعه..."
                        autocomplete="off"
                        class="w-full rounded-2xl border border-line bg-white py-3.5 pe-4 ps-12 text-sm text-ink shadow-card outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
                    >
                </div>
                <p id="home-part-search-empty" class="mt-3 hidden text-sm text-ink-muted">قطعه‌ای با این نام یافت نشد.</p>
            </div>

            <div id="home-parts-grid" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($parts as $part)
                    <a
                        href="{{ route('part.show', $part->slug) }}"
                        class="home-part-card ps-card-interactive flex flex-col p-5"
                        data-part-name="{{ $part->name }}"
                    >
                        <div class="mb-3 flex size-11 items-center justify-center rounded-xl bg-brand-soft text-brand">
                            <i class="fa-solid fa-gear" aria-hidden="true"></i>
                        </div>
                        <h3 class="text-base font-semibold text-ink">{{ $part->name }}</h3>
                        @if ($part->partsCategory)
                            <p class="mt-1 text-xs font-medium text-brand">{{ $part->partsCategory->name }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        @endif
    </section>
@endsection

@push('scripts')
    <script>
        (function () {
            const companySearchInput = document.getElementById('home-company-search');
            const companyEmptyMessage = document.getElementById('home-company-search-empty');
            const companyCards = Array.from(document.querySelectorAll('.home-company-card'));

            if (companySearchInput && companyCards.length > 0) {
                companySearchInput.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    let visibleCount = 0;

                    companyCards.forEach(function (card) {
                        const name = (card.dataset.companyName || '').toLowerCase();
                        const matches = query === '' || name.includes(query);

                        card.classList.toggle('hidden', !matches);

                        if (matches) {
                            visibleCount++;
                        }
                    });

                    if (companyEmptyMessage) {
                        companyEmptyMessage.classList.toggle('hidden', query === '' || visibleCount > 0);
                    }
                });
            }

            const partSearchInput = document.getElementById('home-part-search');
            const partEmptyMessage = document.getElementById('home-part-search-empty');
            const partCards = Array.from(document.querySelectorAll('.home-part-card'));

            if (partSearchInput && partCards.length > 0) {
                partSearchInput.addEventListener('input', function () {
                    const query = this.value.trim().toLowerCase();
                    let visibleCount = 0;

                    partCards.forEach(function (card) {
                        const name = (card.dataset.partName || '').toLowerCase();
                        const matches = query === '' || name.includes(query);

                        card.classList.toggle('hidden', !matches);

                        if (matches) {
                            visibleCount++;
                        }
                    });

                    if (partEmptyMessage) {
                        partEmptyMessage.classList.toggle('hidden', query === '' || visibleCount > 0);
                    }
                });
            }
        })();
    </script>
@endpush
