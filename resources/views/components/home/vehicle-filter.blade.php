@props([
    'vehicleFilter' => [
        'companies' => [],
        'carsByCompany' => [],
        'modelsByCar' => [],
        'parts' => [],
    ],
])

@php
    $companies = $vehicleFilter['companies'] ?? [];
    $parts = $vehicleFilter['parts'] ?? [];
@endphp

@if ($companies !== [])
    <section id="vehicle-filter" class="scroll-mt-24 mb-10">
        <div class="relative overflow-hidden rounded-2xl border border-brand-soft/35 bg-gradient-to-bl from-[#fff4eb] via-[#fffaf5] to-white shadow-[0_8px_28px_-12px_rgb(242_124_34_/_0.35)]">
            <div class="pointer-events-none absolute inset-y-0 start-0 w-1.5 bg-brand-soft" aria-hidden="true"></div>

            <div class="relative flex flex-col justify-center px-4 py-4 sm:px-6 sm:py-5">
                <div class="min-w-0">
                    <p class="ps-section-label">انتخاب خودرو</p>
                    <h2 class="ps-section-title mt-0.5 text-lg sm:text-xl">قطعه مناسب خودروی خود را پیدا کنید</h2>
                    <p class="mt-1 text-xs text-ink-muted sm:text-sm">برند، خودرو و مدل را انتخاب کنید. انتخاب قطعه اختیاری است.</p>
                </div>

                <form
                    id="home-vehicle-filter-form"
                    class="mt-3.5"
                    data-vehicle-filter
                    data-cars-by-company='@json($vehicleFilter['carsByCompany'] ?? [])'
                    data-models-by-car='@json($vehicleFilter['modelsByCar'] ?? [])'
                    data-parts='@json($parts)'
                    data-product-url-prefix="{{ url('/product') }}"
                    data-label-parts="مشاهده قطعات"
                    data-label-shops="مشاهده فروشگاه‌ها"
                    data-no-progress
                >
                    <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-2 lg:grid-cols-4 sm:gap-3">
                        <div class="min-w-0">
                            <label for="vehicle-company" class="mb-1 block text-xs font-medium text-ink-muted">انتخاب کمپانی</label>
                            <div class="ps-searchable-select">
                                <select
                                    id="vehicle-company"
                                    name="company"
                                    data-vehicle-company
                                    data-searchable-select
                                    required
                                >
                                    <option value="">انتخاب کمپانی سازنده</option>
                                    @foreach ($companies as $company)
                                        <option value="{{ $company['slug'] }}">{{ $company['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="min-w-0">
                            <label for="vehicle-car" class="mb-1 block text-xs font-medium text-ink-muted">خودرو</label>
                            <div class="ps-searchable-select">
                                <select
                                    id="vehicle-car"
                                    name="car"
                                    data-vehicle-car
                                    data-searchable-select
                                    disabled
                                    required
                                >
                                    <option value="">انتخاب خودرو</option>
                                </select>
                            </div>
                        </div>

                        <div class="min-w-0">
                            <label for="vehicle-model" class="mb-1 block text-xs font-medium text-ink-muted">مدل</label>
                            <div class="ps-searchable-select">
                                <select
                                    id="vehicle-model"
                                    name="model"
                                    data-vehicle-model
                                    data-searchable-select
                                    disabled
                                    required
                                >
                                    <option value="">انتخاب مدل</option>
                                </select>
                            </div>
                        </div>

                        <div class="min-w-0">
                            <label for="vehicle-part" class="mb-1 block text-xs font-medium text-ink-muted">قطعه <span class="font-normal text-ink-muted/80">(اختیاری)</span></label>
                            <div class="ps-searchable-select">
                                <select
                                    id="vehicle-part"
                                    name="part"
                                    data-vehicle-part
                                    data-searchable-select
                                    disabled
                                >
                                    <option value="">همه قطعات</option>
                                    @foreach ($parts as $part)
                                        <option value="{{ $part['slug'] }}">{{ $part['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3.5 flex flex-wrap items-center gap-2.5">
                        <button
                            type="submit"
                            class="ps-btn-primary transition duration-300 ease-out"
                            data-vehicle-filter-submit
                            disabled
                        >
                            <span data-vehicle-filter-submit-label aria-live="polite">مشاهده قطعات</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>
@endif
