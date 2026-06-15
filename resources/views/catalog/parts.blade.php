@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <x-site.breadcrumb :items="$breadcrumbs" />

            <x-ui.section-heading
                label="قطعات"
                :title="$title"
                :description="$description"
                heading="h1"
            />
        </div>
    </div>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-ink-muted">{{ number_format($parts->total()) }} قطعه</p>
        <x-catalog.vehicle-nav :context="$context" active="parts" />
    </div>

    <x-catalog.context-summary :context="$context" :clear-url="route('car.parts')" />

    <x-catalog.filter-card
        class="space-y-4"
        data-catalog-type="parts"
        data-catalog-parts-base="{{ route('car.parts') }}"
        data-catalog-parts-vehicle-template="{{ route('car.parts.vehicle', ['company' => '__COMPANY__', 'car' => '__CAR__', 'model' => '__MODEL__']) }}"
        :clear-url="($context->company || $context->car || $context->model || $filters['q'] || $filters['category']) ? route('car.parts') : null"
    >
        <div class="grid gap-4 sm:grid-cols-3">
            <div>
                <label for="parts-company-filter" class="mb-2 block text-sm font-medium text-ink">برند</label>
                <select
                    id="parts-company-filter"
                    data-catalog-field="company"
                    class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
                >
                    <option value="">همه برندها</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->slug }}" @selected($context->company?->slug === $company->slug)>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="parts-car-filter" class="mb-2 block text-sm font-medium text-ink">خودرو</label>
                <select
                    id="parts-car-filter"
                    data-catalog-field="car"
                    class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
                >
                    <option value="">همه خودروها</option>
                    @foreach ($cars as $car)
                        <option value="{{ $car->slug }}" @selected($context->car?->slug === $car->slug)>
                            {{ $context->company ? $car->name : $car->company->name.' '.$car->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <x-catalog.model-category-selects
            :groups="$modelCategoryGroups"
            :selected-model-slug="$context->model?->slug"
            category-field-id="parts-model-category-filter"
            model-field-id="parts-model-filter"
        />

        <div class="relative">
            <label for="parts-search" class="sr-only">جستجوی قطعه</label>
            <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
                id="parts-search"
                type="search"
                name="q"
                data-catalog-query-field
                value="{{ $filters['q'] }}"
                placeholder="جستجوی نام قطعه..."
                class="w-full rounded-xl border border-line bg-white py-3 pe-4 ps-12 text-sm text-ink outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
            >
        </div>

        @if ($categories->isNotEmpty())
            <div>
                <label for="parts-category" class="mb-2 block text-sm font-medium text-ink">دسته‌بندی</label>
                <select
                    id="parts-category"
                    name="category"
                    data-catalog-query-field
                    class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
                >
                    <option value="">همه دسته‌ها</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($filters['category'] === $category->id)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </x-catalog.filter-card>

    @if ($parts->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">قطعه‌ای با این فیلتر یافت نشد.</p>
        </div>
    @else
        <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
            @foreach ($parts as $part)
                <x-ui.part-card :part="$part" :url="$part->catalog_url" />
            @endforeach
        </div>

        <div class="mt-10">
            {{ $parts->links() }}
        </div>
    @endif
@endsection
