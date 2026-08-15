@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-site.breadcrumb :items="$breadcrumbs" />

    <div class="mb-8 flex flex-col gap-6 md:flex-row md:items-start md:gap-8">
        <div class="min-w-0 flex-1">
            <div class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
                <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
                    <x-ui.section-heading
                        label="خودرو"
                        :title="$title"
                        description="برند خودروی خود را انتخاب کنید تا خودروها و مدل‌های آن را ببینید"
                        heading="h1"
                    />
                </div>
            </div>
        </div>

        <x-site.sticky-cta-sidebar />
    </div>

    <div
        class="mb-6"
        data-no-progress
        x-data="catalogClientSearch({
            itemSelector: '.catalog-company-card',
            minChars: {{ \App\Support\CatalogSearch::MIN_CHARS }},
            debounceMs: {{ \App\Support\CatalogSearch::CLIENT_DEBOUNCE_MS }},
        })"
    >
        <x-catalog.search-bar
            id="company-search"
            placeholder="جستجوی نام برند خودرو..."
            empty-message="برند خودرویی با این نام یافت نشد."
            alpine
            class="mb-0"
        />

        @if ($companies->isEmpty())
            <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
                <p class="text-sm text-ink-muted">برند خودرویی برای نمایش ثبت نشده است.</p>
            </div>
        @else
            <div id="companies-grid" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($companies as $company)
                    <a
                        href="{{ route('cars.index', ['company' => $company->slug]) }}"
                        class="catalog-company-card ps-card-interactive flex flex-col p-5"
                        data-search-text="{{ $company->name }}"
                    >
                        <x-ui.company-logo
                            class="mb-3"
                            :name="$company->name"
                            :logo-url="$company->logo_url ?? null"
                        />
                        <h2 class="text-base font-semibold text-ink">{{ $company->name }}</h2>
                        <p class="mt-1 text-xs text-ink-muted">{{ $company->cars_count }} خودرو</p>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
@endsection
