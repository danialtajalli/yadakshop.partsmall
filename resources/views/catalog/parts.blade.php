@extends('layouts.app')

@if (isset($_GET['page']) && $_GET['page'] > 1 && $parts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
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

    <form method="GET" action="{{ url()->current() }}" id="parts-search-form" class="mb-6">
        <x-catalog.search-bar
            id="parts-search"
            name="q"
            :value="$filters['q'] ?? ''"
            placeholder="جستجوی نام قطعه..."
            :clear-url="request()->url()"
            class="mb-0"
        />
    </form>

    @if ($parts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="text-sm text-ink-muted">
                {{ number_format($parts->total()) }} مورد یافت شد
            </p>
        </div>
    @endif

    @if ($parts->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">قطعه‌ای با این فیلتر یافت نشد.</p>
        </div>
    @else
        <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 2xl:grid-cols-4">
            @foreach ($parts as $part)
                <x-ui.part-card :context="$context" :part="$part" :url="$part->catalog_url" />
            @endforeach
        </div>

        @if ($parts instanceof \Illuminate\Contracts\Pagination\LengthAwarePaginator)
            <div class="mt-10">
                {{ $parts->links() }}
            </div>
        @endif
    @endif
@endsection
