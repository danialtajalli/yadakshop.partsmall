@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-brand-soft via-white to-white px-5 py-6 sm:px-8 sm:py-8">
            <x-site.breadcrumb :items="[
                ['label' => 'خانه', 'url' => url('/')],
                ['label' => $company->name, 'emphasized' => true],
                ['label' => $car->name],
                ['label' => $model->name, 'active' => true, 'url' => route('car.parts', ['company' => $company->slug, 'car' => $car->slug, 'model' => $model->slug])],
            ]" />

            <x-ui.section-heading
                label="انتخاب قطعه"
                :title="$company->name . ' · ' . $car->name . ' · ' . $model->name"
                description="قطعه مورد نظر خود را جستجو کنید یا از لیست انتخاب نمایید"
                heading="h1"
            />
        </div>
    </div>

    @if ($car->description)
        <div class="mb-8 ps-card px-5 py-6 sm:px-6">
            <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-brand">
                معرفی خودرو {{ $company->name }} {{ $car->name }}
            </h2>
            <x-ui.expandable-description id="car-description">
                {!! $car->description !!}
            </x-ui.expandable-description>
        </div>
    @endif

    <x-ui.section-heading
        class="mb-4"
        title="قطعات موجود"
        description="برای یافتن سریع‌تر، نام قطعه را جستجو کنید"
    />

    <div class="mb-8">
        <label for="part-search" class="sr-only">جستجوی قطعه</label>
        <div class="relative">
            <svg class="pointer-events-none absolute start-4 top-1/2 size-5 -translate-y-1/2 text-ink-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
            <input
                id="part-search"
                type="search"
                placeholder="جستجوی نام قطعه..."
                autocomplete="off"
                class="w-full rounded-2xl border border-line bg-white py-3.5 pe-4 ps-12 text-sm text-ink shadow-card outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20"
            >
        </div>
        <p id="part-search-empty" class="mt-3 hidden text-sm text-ink-muted">قطعه‌ای با این نام یافت نشد.</p>
    </div>

    @if ($parts->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">قطعه‌ای برای نمایش ثبت نشده است.</p>
        </div>
    @else
        <div id="parts-grid" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($parts as $part)
                <article
                    class="part-card ps-card-interactive flex flex-col p-5"
                    data-part-name="{{ $part->name }}"
                >
                    <div class="mb-4 min-w-0 flex-1">
                        @if ($part->partsCategory)
                            <p class="mb-2 text-xs font-medium text-brand">{{ $part->partsCategory->name }}</p>
                        @endif
                        <h3 class="text-lg font-semibold text-ink">{{ $part->name }}</h3>
                    </div>

                    <a
                        href="{{ route('product.show', [
                            'company' => $company->slug,
                            'car' => $car->slug,
                            'model' => $model->slug,
                            'part' => $part->slug,
                        ]) }}"
                        class="ps-btn-primary w-full text-center"
                    >
                        مشاهده جزئیات
                    </a>
                </article>
            @endforeach
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        (function () {
            const searchInput = document.getElementById('part-search');
            const emptyMessage = document.getElementById('part-search-empty');
            const cards = Array.from(document.querySelectorAll('.part-card'));

            if (!searchInput || cards.length === 0) {
                return;
            }

            searchInput.addEventListener('input', function () {
                const query = this.value.trim().toLowerCase();
                let visibleCount = 0;

                cards.forEach(function (card) {
                    const name = (card.dataset.partName || '').toLowerCase();
                    const matches = query === '' || name.includes(query);

                    card.classList.toggle('hidden', !matches);

                    if (matches) {
                        visibleCount++;
                    }
                });

                if (emptyMessage) {
                    emptyMessage.classList.toggle('hidden', query === '' || visibleCount > 0);
                }
            });
        })();
    </script>
@endpush
