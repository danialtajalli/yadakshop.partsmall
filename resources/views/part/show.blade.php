@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <x-site.breadcrumb :items="[
                ['label' => 'خانه', 'url' => route('home')],
                ['label' => 'برندها', 'url' => route('companies.index')],
                ['label' => 'قطعات', 'url' => route('parts.index')],
                ['label' => $part->name, 'active' => true, 'url' => route('part.show', $part->slug)],
            ]" />

            <x-ui.section-heading
                label="قطعه"
                :title="$part->name"
                :description="$part->partsCategory?->name"
                heading="h1"
            />
        </div>
    </div>

    <x-ui.section-heading
        class="mb-4"
        title="خودروها و مدل‌های مرتبط"
        description="برای مشاهده قیمت، فروشگاه‌ها و جزئیات، خودروی مورد نظر را انتخاب کنید"
    />

    <form method="GET" action="{{ route('part.show', $part->slug) }}" class="ps-card mb-6 p-5 sm:p-6">
        <div class="relative">
            <label for="vehicle-search" class="sr-only">جستجوی خودرو</label>
            <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
                id="vehicle-search"
                type="search"
                name="q"
                value="{{ $filters['q'] ?? '' }}"
                placeholder="جستجوی نام خودرو یا شرکت..."
                class="w-full rounded-xl border border-line bg-white py-3 pe-4 ps-12 text-sm text-ink outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
            >
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button type="submit" class="ps-btn-primary">جستجو</button>
            @if ($filters['q'] ?? null)
                <a href="{{ route('part.show', $part->slug) }}" class="ps-btn-secondary">پاک کردن</a>
            @endif
        </div>
    </form>

    <div class="mb-4 flex items-center justify-between gap-3">
        <p class="text-sm text-ink-muted">
            {{ number_format($vehicleApplications->total()) }} مورد یافت شد
        </p>
    </div>

    @if ($vehicleApplications->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center mb-6">
            <p class="text-sm text-ink-muted">
                @if ($filters['q'] ?? null)
                    خودرویی با این نام یافت نشد.
                @else
                    خودرویی برای نمایش ثبت نشده است.
                @endif
            </p>
        </div>
    @else
        <div class="divide-y divide-line overflow-hidden rounded-2xl border border-line bg-white shadow-card mb-6">
            @foreach ($vehicleApplications as $application)
                <a
                    href="{{ $application['url'] }}"
                    class="flex items-center justify-between gap-4 px-5 py-4 text-sm transition hover:bg-brand-soft/40 sm:px-6"
                >
                    <span class="font-medium text-ink">{{ $application['label'] }}</span>
                    <span class="inline-flex shrink-0 items-center gap-1.5 text-brand">
                        مشاهده
                        <i class="fa-solid fa-arrow-left text-xs" aria-hidden="true"></i>
                    </span>
                </a>
            @endforeach
        </div>

        @if ($part->description || $part->category_description)
        <div class="mb-8 space-y-6">
            @if ($part->description)
                <section class="ps-card px-5 py-6 sm:px-6">
                    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-brand">معرفی {{ $part->name }}</h2>
                    <x-ui.expandable-description id="part-description">
                        {!! $part->description !!}
                    </x-ui.expandable-description>
                </section>
            @endif

            @if ($part->category_description)
                <section class="ps-card px-5 py-6 sm:px-6">
                    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-brand">
                        @if ($part->partsCategory)
                            درباره دسته {{ $part->partsCategory->name }}
                        @else
                            توضیحات دسته‌بندی
                        @endif
                    </h2>
                    <x-ui.expandable-description id="part-category-description">
                        {!! $part->category_description !!}
                    </x-ui.expandable-description>
                </section>
            @endif
        </div>
        @endif

        <div class="mt-10">
            {{ $vehicleApplications->withQueryString()->links() }}
        </div>
    @endif
@endsection
