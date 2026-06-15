@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <x-site.breadcrumb :items="[
                ['label' => 'خانه', 'url' => route('home')],
                ['label' => 'قطعات', 'url' => route('car.parts')],
                ['label' => $part->name],
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
        <div class="mb-6 rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">
                @if ($filters['q'] ?? null)
                    خودرویی با این نام یافت نشد.
                @else
                    خودرویی برای نمایش ثبت نشده است.
                @endif
            </p>
        </div>
    @else
        <div
            class="mb-6 columns-2 gap-2 sm:columns-3 md:columns-4 lg:columns-5 xl:columns-6 2xl:columns-7"
            role="list"
            aria-label="خودروها و مدل‌های مرتبط"
        >
            @foreach ($vehicleApplications as $application)
                <a
                    href="{{ $application['url'] }}"
                    role="listitem"
                    title="{{ $application['label'] }}"
                    class="mb-2 inline-flex w-full break-inside-avoid items-center rounded-lg border border-line bg-white px-2.5 py-1.5 text-xs font-medium leading-snug text-ink transition hover:border-brand/40 hover:bg-brand-soft/50 sm:px-3 sm:py-2 sm:text-sm"
                >
                    {{ $application['short_label'] }}
                </a>
            @endforeach
        </div>

        <div class="mt-10">
            {{ $vehicleApplications->withQueryString()->links() }}
        </div>
    @endif

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
@endsection
