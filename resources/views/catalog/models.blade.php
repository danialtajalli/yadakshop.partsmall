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
        <p class="text-sm text-ink-muted">{{ number_format($models->count()) }} مدل</p>
        <x-catalog.vehicle-nav :context="$context" active="models" />
    </div>

    <x-catalog.context-summary :context="$context" :clear-url="route('models.index')" />

    <x-catalog.filter-card
        data-catalog-type="models"
        data-catalog-models-base="{{ route('models.index') }}"
        data-catalog-models-company-template="{{ route('models.index', ['company' => '__COMPANY__']) }}"
        data-catalog-models-car-template="{{ route('models.index', ['company' => '__COMPANY__', 'car' => '__CAR__']) }}"
        :clear-url="($context->company || $context->car) ? route('models.index') : null"
    >
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="models-company-filter" class="mb-2 block text-sm font-medium text-ink">کمپانی</label>
                <select
                    id="models-company-filter"
                    data-catalog-field="company"
                    class="w-full rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
                >
                    <option value="">همه کمپانی ها</option>
                    @foreach ($companies as $company)
                        <option value="{{ $company->slug }}" @selected($context->company?->slug === $company->slug)>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="models-car-filter" class="mb-2 block text-sm font-medium text-ink">خودرو</label>
                <select
                    id="models-car-filter"
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
    </x-catalog.filter-card>

    @if ($modelCategoryGroups->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">مدلی با این فیلتر یافت نشد.</p>
        </div>
    @else
        <x-catalog.model-category-explorer :groups="$modelCategoryGroups" />
    @endif
@endsection
