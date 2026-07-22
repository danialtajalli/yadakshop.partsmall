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
                        :description="$description"
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
            itemSelector: '[data-search-text]',
            sectionSelector: '.model-category-section',
            minChars: {{ \App\Support\CatalogSearch::MIN_CHARS }},
            debounceMs: {{ \App\Support\CatalogSearch::CLIENT_DEBOUNCE_MS }},
        })"
    >
        <x-catalog.search-bar
            id="model-search"
            placeholder="جستجوی نام مدل..."
            empty-message="مدلی با این نام یافت نشد."
            alpine
            class="mb-0"
        />

        @if ($modelCategoryGroups->isEmpty())
            <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
                <p class="text-sm text-ink-muted">مدلی با این فیلتر یافت نشد.</p>
            </div>
        @else
            <x-catalog.model-category-list :groups="$modelCategoryGroups" />
        @endif
    </div>
@endsection
