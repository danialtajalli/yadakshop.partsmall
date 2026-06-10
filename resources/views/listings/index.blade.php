@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <x-site.breadcrumb :items="[
                ['label' => 'خانه', 'url' => url('/')],
                ['label' => $title, 'active' => true],
            ]" />

            <x-ui.section-heading
                :label="match ($type) {
                    'repair_shop' => 'تعمیرگاه‌ها',
                    'representation' => 'نمایندگی‌ها',
                    default => 'فروشگاه‌ها',
                }"
                :title="$title"
                :description="match ($type) {
                    'repair_shop' => 'جستجو و فیلتر تعمیرگاه‌ها بر اساس استان، شهر و تخصص',
                    'representation' => 'جستجو و فیلتر نمایندگی‌های رسمی بر اساس استان، شهر و برند',
                    default => 'جستجو و فیلتر فروشگاه‌های لوازم یدکی بر اساس استان و شهر',
                }"
                heading="h1"
            />
        </div>
    </div>

    <x-listings.filters
        :action="route(match ($type) {
            'repair_shop' => 'repair-shops.index',
            'representation' => 'representations.index',
            default => 'shops.index',
        })"
        :filters="$filters"
        :states="$states"
        :cities="$cities"
        :cities-by-state="$citiesByState"
        :specializations="$specializations"
        :show-specialization-filter="$showSpecializationFilter"
    />

    <div class="mb-4 flex items-center justify-between gap-3">
        <p class="text-sm text-ink-muted">
            {{ number_format($listings->total()) }} مورد یافت شد
        </p>
        <div class="flex flex-wrap gap-2 text-sm">
            <a
                href="{{ route('shops.index', request()->except('page')) }}"
                @class([
                    'rounded-lg px-3 py-1.5 transition',
                    'bg-brand text-white' => $type === 'shop',
                    'text-ink-muted hover:bg-surface hover:text-ink' => $type !== 'shop',
                ])
            >
                فروشگاه‌ها
            </a>
            <a
                href="{{ route('repair-shops.index', request()->except('page')) }}"
                @class([
                    'rounded-lg px-3 py-1.5 transition',
                    'bg-brand text-white' => $type === 'repair_shop',
                    'text-ink-muted hover:bg-surface hover:text-ink' => $type !== 'repair_shop',
                ])
            >
                تعمیرگاه‌ها
            </a>
            <a
                href="{{ route('representations.index', request()->except('page')) }}"
                @class([
                    'rounded-lg px-3 py-1.5 transition',
                    'bg-brand text-white' => $type === 'representation',
                    'text-ink-muted hover:bg-surface hover:text-ink' => $type !== 'representation',
                ])
            >
                نمایندگی‌ها
            </a>
        </div>
    </div>

    @if ($listings->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">موردی با این فیلترها یافت نشد.</p>
        </div>
    @else
        <h2 class="sr-only">نتایج جستجو</h2>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($listings as $listing)
                <x-listings.card :listing="$listing" :type="$type" />
            @endforeach
        </div>

        <div class="mt-10">
            {{ $listings->links() }}
        </div>
    @endif
@endsection
