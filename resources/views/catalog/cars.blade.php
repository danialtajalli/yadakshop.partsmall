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

    <x-catalog.context-summary :context="$context" clear-route="cars.index" />

    <div class="ps-card mb-6 p-5 sm:p-6">
        <label for="company-filter" class="mb-2 block text-sm font-medium text-ink">فیلتر برند</label>
        <div class="flex flex-wrap items-center gap-3">
            <select
                id="company-filter"
                name="company"
                class="min-w-[12rem] rounded-xl border border-line bg-white px-3 py-2.5 text-sm text-ink outline-none focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
            >
                <option value="">همه برندها</option>
                @foreach ($companies as $company)
                    <option value="{{ $company->slug }}" @selected($context->company?->slug === $company->slug)>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
            <a id="company-selection-apply" href="" class="ps-btn-primary">اعمال</a>
            @if ($context->company)
                <a href="{{ route('cars.index') }}" class="ps-btn-secondary">پاک کردن</a>
            @endif
        </div>
    </div>

    @if ($cars->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">خودرویی با این فیلتر یافت نشد.</p>
        </div>
    @else
        <div class="divide-y divide-line overflow-hidden rounded-2xl border border-line bg-white shadow-card">
            @foreach ($cars as $car)
                <a
                    href="{{ route('models.index', ['company' => $car->company->slug, 'car' => $car->slug]) }}"
                    class="flex items-center justify-between gap-4 px-5 py-4 text-sm transition hover:bg-brand-soft/40 sm:px-6"
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
    @push('scripts')
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            document.getElementById('company-selection-apply').addEventListener('click', function(e) {
                e.preventDefault();
                console.log('here');
                const company = document.getElementById('company-filter').value;
                if (company) {
                    window.location.href = "{{ route('cars.index', ['company' => ':company']) }}".replace(':company', company);
                } else {
                    window.location.href = "{{ route('cars.index') }}";
                }
            });
        });
    </script>
    @endpush('scripts')
@endsection
