@extends('layouts.app')

@php
    $partsArePaginated = $parts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator;
@endphp

@if (isset($_GET['page']) && $_GET['page'] > 1 && $partsArePaginated)
@push('head')
<meta name="robots" content="noindex, follow" />
@endpush
@endif

@section('title', $title)

@section('content')
    <x-site.breadcrumb :items="$breadcrumbs" />

    <div class="mb-8 flex flex-col gap-6 md:flex-row md:items-start md:gap-8">
        <div class="min-w-0 flex-1">
            <div class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
                <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
                    <x-ui.section-heading
                        label="قطعات"
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
        @if ($partsArePaginated)
            x-data="catalogRemoteSearch({
                action: @js(url()->current()),
                initialQuery: @js($filters['q'] ?? ''),
                csrf: @js(csrf_token()),
                minChars: {{ \App\Support\CatalogSearch::MIN_CHARS }},
                debounceMs: {{ \App\Support\CatalogSearch::DEBOUNCE_MS }},
            })"
        @else
            x-data="catalogClientSearch({
                itemSelector: '.catalog-part-card',
                minChars: {{ \App\Support\CatalogSearch::MIN_CHARS }},
                debounceMs: {{ \App\Support\CatalogSearch::DEBOUNCE_MS }},
            })"
        @endif
    >
        <form
            method="POST"
            action="{{ url()->current() }}"
            data-no-progress
            @submit.prevent="scheduleSearch({ force: true })"
            class="mb-6"
        >
            @csrf
            <x-catalog.search-bar
                id="parts-search"
                name="q"
                :value="$filters['q'] ?? ''"
                placeholder="جستجوی نام قطعه..."
                alpine
                class="mb-0"
                :empty-message="$partsArePaginated ? null : 'قطعه‌ای با این نام یافت نشد.'"
            />
        </form>

        <div data-catalog-search-results>
            @if ($partsArePaginated)
                <div class="mb-4 flex items-center justify-between gap-3">
                    <p class="text-sm text-ink-muted">
                        {{ number_format($parts->total()) }} مورد یافت شد
                    </p>
                </div>
            @endif

            @if ($parts->isEmpty())
                <x-catalog.search-empty
                    message="قطعه‌ای با این فیلتر یافت نشد."
                    alpine
                    boxed
                />
            @else
                <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 2xl:grid-cols-4">
                    @foreach ($parts as $part)
                        <x-ui.part-card
                            class="catalog-part-card"
                            data-search-text="{{ $part->title }} {{ $part->partsCategory?->name }}"
                            :context="$context"
                            :part="$part"
                            :url="$part->catalog_url"
                        />
                    @endforeach
                </div>

                @if ($partsArePaginated)
                    <div class="mt-10">
                        {{ $parts->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
