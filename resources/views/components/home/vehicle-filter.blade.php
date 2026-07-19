@props([
    'vehicleFilter' => [
        'companies' => [],
        'carsByCompany' => [],
        'modelsByCar' => [],
    ],
])

@php
    $companies = $vehicleFilter['companies'] ?? [];
@endphp

@if ($companies !== [])
    <section id="vehicle-filter" class="scroll-mt-24 mb-10">
        <div class="relative overflow-hidden rounded-2xl border border-brand-soft/35 bg-gradient-to-bl from-[#fff4eb] via-[#fffaf5] to-white shadow-[0_8px_28px_-12px_rgb(242_124_34_/_0.35)]">
            <div class="pointer-events-none absolute inset-y-0 start-0 w-1.5 bg-brand-soft" aria-hidden="true"></div>

            <div class="grid items-stretch sm:grid-cols-[minmax(0,1fr)_10.5rem] md:grid-cols-[minmax(0,1fr)_12.5rem] lg:grid-cols-[minmax(0,1fr)_14rem]">
                <div class="relative flex flex-col justify-center px-4 py-4 sm:px-6 sm:py-5">
                    <div class="min-w-0">
                        <p class="ps-section-label">انتخاب خودرو</p>
                        <h2 class="ps-section-title mt-0.5 text-lg sm:text-xl">قطعه مناسب خودروی خود را پیدا کنید</h2>
                        <p class="mt-1 text-xs text-ink-muted sm:text-sm">برند، خودرو و مدل را انتخاب کنید.</p>
                    </div>

                    <form
                        id="home-vehicle-filter-form"
                        class="mt-3.5"
                        data-vehicle-filter
                        data-cars-by-company='@json($vehicleFilter['carsByCompany'] ?? [])'
                        data-models-by-car='@json($vehicleFilter['modelsByCar'] ?? [])'
                        data-no-progress
                    >
                        <div class="grid grid-cols-1 gap-2.5 sm:grid-cols-3 sm:gap-3">
                            <div class="min-w-0">
                                <label for="vehicle-company" class="mb-1 block text-xs font-medium text-ink-muted">برند</label>
                                <div class="ps-searchable-select">
                                    <select
                                        id="vehicle-company"
                                        name="company"
                                        data-vehicle-company
                                        data-searchable-select
                                        required
                                    >
                                        <option value="">انتخاب برند</option>
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
                        </div>

                        <div class="mt-3.5 flex flex-wrap items-center gap-2.5">
                            <button
                                type="submit"
                                class="ps-btn-primary"
                                data-vehicle-filter-submit
                                disabled
                            >
                                مشاهده قطعات
                            </button>
                            <a href="{{ route('car.parts') }}" class="ps-btn-secondary">
                                همه قطعات
                            </a>
                        </div>
                    </form>
                </div>

                <div class="relative flex min-h-44 items-center justify-center overflow-hidden bg-[#1a202c] sm:min-h-0 sm:border-s sm:border-brand-soft/20">
                    <img
                        src="{{ asset('img/contact.webp') }}"
                        alt="قطعات و لوازم یدکی خودرو"
                        width="1024"
                        height="1536"
                        class="max-h-full w-full object-contain object-center p-2 sm:absolute sm:inset-0 sm:size-full sm:p-3"
                        loading="lazy"
                        decoding="async"
                    >
                </div>
            </div>
        </div>
    </section>
@endif
