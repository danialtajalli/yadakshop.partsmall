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

    <form method="GET" action="{{ url()->current() }}" id="parts-search-form">
        <x-catalog.search-bar
            id="parts-search"
            name="q"
            :value="$filters['q'] ?? ''"
            placeholder="جستجوی نام قطعه..."
            class="mb-6"
        />
    </form>

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

@push('scripts')
    <script>
        (function () {
            const form = document.getElementById('parts-search-form');
            const searchInput = document.getElementById('parts-search');

            if (!form || !searchInput) {
                return;
            }

            let timeoutId = null;

            searchInput.addEventListener('input', function () {
                window.clearTimeout(timeoutId);
                timeoutId = window.setTimeout(function () {
                    form.submit();
                }, 400);
            });
        })();
    </script>
@endpush
