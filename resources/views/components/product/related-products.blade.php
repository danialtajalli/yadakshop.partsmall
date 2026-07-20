@props([
    'products',
    'company',
    'car',
    'model',
])

@if ($products->isNotEmpty())
    @php
        $modelLabel = is_numeric($model->name) ? 'سال '.$model->name : $model->name;
        $vehicleLabel = trim($company->name.' '.$car->name.' '.$modelLabel);
        $allPartsUrl = \App\Support\CatalogUrls::parts($company->slug, $car->slug, $model->slug);
    @endphp

    <section class="mt-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card" data-related-products>
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white to-white px-5 py-5 sm:px-6">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="min-w-0">
                    <h2 class="ps-section-title text-2xl font-bold tracking-tight text-ink sm:text-3xl">محصولات مرتبط</h2>
                    <p class="mt-1.5 text-sm text-ink-muted">قطعات دیگر برای {{ $vehicleLabel }}</p>
                </div>

                <a href="{{ $allPartsUrl }}" class="ps-btn-secondary shrink-0 px-3 py-2 text-xs">
                    مشاهده همه قطعات
                </a>
            </div>
        </div>

        <div class="grid gap-2 p-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ($products as $relatedPart)
                <x-ui.part-card
                    :part="$relatedPart"
                    :url="$relatedPart->url"
                    :context="null"
                />
            @endforeach
        </div>
    </section>
@endif
