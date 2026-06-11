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

    <x-catalog.context-summary :context="$context" clear-route="parts.index" />

    <form method="GET" action="{{ route('parts.index') }}" class="ps-card mb-6 space-y-4 p-5 sm:p-6">
        @foreach ($context->queryParams() as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach

        <div class="relative">
            <label for="parts-search" class="sr-only">جستجوی قطعه</label>
            <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
                id="parts-search"
                type="search"
                name="q"
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

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="ps-btn-primary">جستجو</button>
            @if ($filters['q'] || $filters['category'])
                <a href="{{ route('parts.index', $context->queryParams()) }}" class="ps-btn-secondary">پاک کردن</a>
            @endif
        </div>
    </form>

    @if ($parts->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">قطعه‌ای با این فیلتر یافت نشد.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($parts as $part)
                <a href="{{ $part->catalog_url }}" class="ps-card-interactive flex flex-col p-5">
                    <div class="mb-3 flex size-11 items-center justify-center rounded-xl bg-brand-soft text-brand">
                        <i class="fa-solid fa-gear" aria-hidden="true"></i>
                    </div>
                    <h2 class="text-base font-semibold text-ink">{{ $part->name }}</h2>
                    @if ($part->partsCategory)
                        <p class="mt-1 text-xs font-medium text-brand">{{ $part->partsCategory->name }}</p>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $parts->links() }}
        </div>
    @endif
@endsection
