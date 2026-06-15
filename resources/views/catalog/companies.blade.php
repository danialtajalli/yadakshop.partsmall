@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <x-site.breadcrumb :items="$breadcrumbs" />

            <x-ui.section-heading
                label="خودرو"
                :title="$title"
                description="کمپانی خودروی خود را انتخاب کنید تا خودروها و مدل‌های آن را ببینید"
                heading="h1"
            />
        </div>
    </div>

    <x-catalog.search-bar
        id="company-search"
        placeholder="جستجوی نام کمپانی..."
        empty-message="کمپانیی با این نام یافت نشد."
    />

    @if ($companies->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">کمپانیی برای نمایش ثبت نشده است.</p>
        </div>
    @else
        <div id="companies-grid" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($companies as $company)
                <a
                    href="{{ route('cars.index', ['company' => $company->slug]) }}"
                    class="catalog-company-card ps-card-interactive flex flex-col p-5"
                    data-search-text="{{ $company->name }}"
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

    <x-catalog.client-search
        input-id="company-search"
        empty-id="company-search-empty"
        item-selector=".catalog-company-card"
    />
@endsection
