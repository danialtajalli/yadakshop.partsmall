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

    <form id="model-search-form" class="mb-6">
        <x-catalog.search-bar
            id="model-search"
            placeholder="جستجوی نام مدل..."
            empty-message="مدلی با این نام یافت نشد."
            class="mb-0"
        />
    </form>

    @if ($modelCategoryGroups->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">مدلی با این فیلتر یافت نشد.</p>
        </div>
    @else
        <x-catalog.model-category-list :groups="$modelCategoryGroups" />
    @endif

    <x-catalog.client-search
        form-id="model-search-form"
        input-id="model-search"
        empty-id="model-search-empty"
        item-selector="[data-search-text]"
        section-selector=".model-category-section"
    />
@endsection
