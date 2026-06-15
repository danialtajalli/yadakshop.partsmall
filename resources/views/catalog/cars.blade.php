@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <x-site.breadcrumb :items="$breadcrumbs" />

            <x-ui.section-heading
                label="خودرو"
                :title="$title"
                :description="$description"
                heading="h1"
            />
        </div>
    </div>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-ink-muted">{{ number_format($cars->count()) }} خودرو</p>
        <x-catalog.vehicle-nav :context="$context" active="cars" />
    </div>

    <x-catalog.search-bar
        id="car-search"
        placeholder="جستجوی نام خودرو..."
        empty-message="خودرویی با این نام یافت نشد."
    />

    @if ($cars->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">خودرویی برای نمایش ثبت نشده است.</p>
        </div>
    @else
        <div class="divide-y divide-line overflow-hidden rounded-2xl border border-line bg-white shadow-card">
            @foreach ($cars as $car)
                <a
                    href="{{ route('models.index', ['company' => $car->company->slug, 'car' => $car->slug]) }}"
                    class="catalog-car-row flex items-center justify-between gap-4 px-5 py-4 text-sm transition hover:bg-brand-soft/40 sm:px-6"
                    data-search-text="{{ $car->name }} {{ $car->company->name }}"
                >
                    <span>
                        <span class="block font-medium text-ink">{{ $car->name }}</span>
                        @if (! $context->company)
                            <span class="mt-0.5 block text-xs text-ink-muted">{{ $car->company->name }}</span>
                        @endif
                    </span>
                    <span class="inline-flex shrink-0 items-center gap-1.5 text-brand">
                        {{ $car->models_count }} مدل
                        <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                    </span>
                </a>
            @endforeach
        </div>
    @endif

    <x-catalog.client-search
        input-id="car-search"
        empty-id="car-search-empty"
        item-selector=".catalog-car-row"
    />
@endsection
