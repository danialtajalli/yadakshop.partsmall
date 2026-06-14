@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <x-site.breadcrumb :items="$breadcrumbs" />

            <x-ui.section-heading
                label="خودرو"
                :title="$title"
                description="برند خودروی خود را انتخاب کنید تا خودروها و مدل‌های آن را ببینید"
                heading="h1"
            />
        </div>
    </div>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-ink-muted">{{ number_format($companies->count()) }} برند</p>
        <x-catalog.vehicle-nav :context="$context" active="companies" />
    </div>

    <div class="ps-card mb-6 p-5 sm:p-6">
        <label for="company-search" class="mb-2 block text-sm font-medium text-ink">جستجوی برند</label>
        <div class="relative">
            <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
                id="company-search"
                type="search"
                placeholder="جستجوی نام برند..."
                autocomplete="off"
                class="w-full rounded-xl border border-line bg-white py-3 pe-4 ps-12 text-sm text-ink outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
            >
        </div>
        <p id="company-search-empty" class="mt-3 hidden text-sm text-ink-muted">برندی با این نام یافت نشد.</p>
    </div>

    @if ($companies->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">برندی برای نمایش ثبت نشده است.</p>
        </div>
    @else
        <div id="companies-grid" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($companies as $company)
                <a
                    href="{{ route('cars.index', ['company' => $company->slug]) }}"
                    class="catalog-company-card ps-card-interactive flex flex-col p-5"
                    data-company-name="{{ $company->name }}"
                >
                    <div class="mb-3 flex size-11 items-center justify-center overflow-hidden rounded-xl bg-brand-soft text-brand">
                        @if ($company->logo_url ?? null)
                            <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="size-full object-cover">
                        @else
                            {{ mb_substr($company->name, 0, 1) }}
                        @endif
                    </div>
                    <h2 class="text-base font-semibold text-ink">{{ $company->name }}</h2>
                    <p class="mt-1 text-xs text-ink-muted">{{ $company->cars_count }} خودرو</p>
                </a>
            @endforeach
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (function () {
            const searchInput = document.getElementById('company-search');
            const emptyMessage = document.getElementById('company-search-empty');
            const cards = Array.from(document.querySelectorAll('.catalog-company-card'));

            if (!searchInput || cards.length === 0) {
                return;
            }

            searchInput.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                let visibleCount = 0;

                cards.forEach(function (card) {
                    const name = (card.dataset.companyName || '').toLowerCase();
                    const matches = query === '' || name.includes(query);

                    card.classList.toggle('hidden', !matches);

                    if (matches) {
                        visibleCount++;
                    }
                });

                if (emptyMessage) {
                    emptyMessage.classList.toggle('hidden', query === '' || visibleCount > 0);
                }
            });
        })();
    </script>
@endpush
