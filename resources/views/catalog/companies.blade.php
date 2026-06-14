@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <x-site.breadcrumb :items="$breadcrumbs" />

            <x-ui.section-heading
                label="خودرو"
                :title="$title"
                description="برند خودروی خود را انتخاب کنید تا خودروها و مدل‌های آن را ببینید"
                heading="h1"
            />
        </div>
    </div>

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-ink-muted">{{ number_format($companies->count()) }} کمپانی</p>
        <x-catalog.vehicle-nav :context="$context" active="companies" />
    </div>

    @if ($companies->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">کمپانی برای نمایش ثبت نشده است.</p>
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($companies as $company)
                <a
                    href="{{ route('cars.index', ['company' => $company->slug]) }}"
                    class="ps-card-interactive flex flex-col p-5"
                >
                    <div class="mb-3 flex size-11 items-center justify-center overflow-hidden rounded-xl bg-brand-soft text-brand">
                        @if ($company->logo_url ?? null)
                            <img src="{{ $company->logo_url }}" alt="{{ $company->name }}" class="size-full object-cover">
                        @else
                            {{ mb_substr($company->name, 0, 1) }}
                        @endif
                    </div>
                    <h2 class="text-base font-semibold text-ink">{{ $company->name }}</h2>
                    <p class="mt-1 text-xs text-ink-muted">{{ $company->cars_count }} کمپانی</p>
                </a>
            @endforeach
        </div>
    @endif
@endsection
