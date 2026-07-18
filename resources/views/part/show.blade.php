@extends('layouts.app')

@if (isset($_GET['page']) && $_GET['page'] > 1)
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
                        label="قطعه"
                        :title="$title"
                        :description="$part->partsCategory?->name"
                        heading="h1"
                    />
                </div>
            </div>
        </div>

        <x-site.sticky-cta-sidebar />
    </div>

    <x-ui.section-heading
        class="mb-4"
        title="خودروها و مدل‌های مرتبط"
        description="برای مشاهده قیمت، فروشگاه‌ها و جزئیات، خودروی مورد نظر را انتخاب کنید"
    />

    @if ($vehicleApplications->total() === 0 && ! ($filters['q'] ?? null))
        <div class="mb-6 rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">خودرویی برای نمایش ثبت نشده است.</p>
        </div>
    @else
        <div
            class="mb-6"
            data-no-progress
            x-data="catalogRemoteSearch({
                action: @js(route('part.show', $part->slug)),
                initialQuery: @js($filters['q'] ?? ''),
                csrf: @js(csrf_token()),
                minChars: {{ \App\Support\CatalogSearch::MIN_CHARS }},
                debounceMs: {{ \App\Support\CatalogSearch::DEBOUNCE_MS }},
            })"
        >
            <form
                method="POST"
                action="{{ route('part.show', $part->slug) }}"
                @submit.prevent="scheduleSearch({ force: true })"
                class="mb-6"
            >
                @csrf
                <x-catalog.search-bar
                    id="vehicle-search"
                    name="q"
                    :value="$filters['q'] ?? ''"
                    placeholder="جستجوی نام خودرو یا شرکت..."
                    alpine
                    class="mb-0"
                />
            </form>

            <div data-catalog-search-results>
                <div class="mb-4 flex items-center justify-between gap-3">
                    <p class="text-sm text-ink-muted">
                        {{ number_format($vehicleApplications->total()) }} مورد یافت شد
                    </p>
                </div>

                @if ($vehicleApplications->isEmpty())
                    <x-catalog.search-empty
                        class="mb-6"
                        message="خودرویی با این نام یافت نشد."
                        alpine
                        boxed
                    />
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
                        {{ $vehicleApplications->links() }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if ($part->description || $part->category_description)
        <div class="mb-8 mt-5 space-y-6">
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
