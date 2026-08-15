@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-site.breadcrumb :items="[
        ['label' => 'خانه', 'url' => url('/')],
        ['label' => 'نمایندگی‌ها', 'url' => route('representations.index')],
        ['label' => $representation->name, 'active' => true],
    ]" />

    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                <x-ui.company-logo
                    :name="$representation->name"
                    :logo-url="$representation->logo ?? null"
                    alt="لوگوی نمایندگی {{ $representation->name }}"
                    size="xl"
                />

                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $representation->name }}</h1>

                    @if ($representation->responsible_person_name)
                        <p class="mt-1 text-sm text-ink-muted">{{ $representation->responsible_person_name }}</p>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-ink-muted">
                        @if ($representation->company)
                            <span>{{ $representation->company->name }}</span>
                        @endif
                        @if ($representation->city?->state)
                            <span>{{ $representation->city->state->name }}</span>
                        @endif
                        @if ($representation->city)
                            <span>{{ $representation->city->name }}</span>
                        @endif
                    </div>

                    @if ($representation->address)
                        <p class="mt-3 text-sm leading-7 text-ink-muted">{{ $representation->address }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-12">
        <div class="space-y-8 lg:col-span-8">
            @if ($representation->latitude && $representation->longitude)
                <x-ui.location-map
                    :latitude="$representation->latitude"
                    :longitude="$representation->longitude"
                    :title="$representation->name"
                    :address="$representation->address"
                />
            @endif

            @if ($representation->description)
                <section class="ps-card px-5 py-6 sm:px-6">
                    <x-ui.section-heading title="درباره نمایندگی" />
                    <x-ui.expandable-description id="representation-description">
                        {!! $representation->description !!}
                    </x-ui.expandable-description>
                </section>
            @endif

            @if (count($serviceTypes) > 0)
                <section>
                    <x-ui.section-heading
                        title="خدمات"
                        description="خدمات ارائه‌شده توسط این نمایندگی"
                    />

                    <ul class="flex flex-wrap gap-2">
                        @foreach ($serviceTypes as $service)
                            <li class="rounded-xl bg-gray-100 px-3 py-1.5 text-sm font-medium text-brand-dark">
                                {{ $service }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($representation->company)
                <section>
                    <x-ui.section-heading title="برند مرتبط" />

                    <article class="ps-card flex items-center gap-4 p-4">
                        <x-ui.company-logo
                            :name="$representation->company->name"
                            :logo-url="$representation->company->logo_url ?? null"
                            alt="لوگوی برند خودرو {{ $representation->company->name }}"
                            size="listing"
                        />
                        <div class="min-w-0">
                            <h3 class="font-semibold text-ink">{{ $representation->company->name }}</h3>
                            @if ($representation->company->country)
                                <p class="mt-0.5 text-sm text-ink-muted">{{ $representation->company->country }}</p>
                            @endif
                        </div>
                    </article>
                </section>
            @endif
        </div>

        <aside class="space-y-6 lg:col-span-4">
            @if (count($contacts) > 0)
                <section class="ps-card p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">تماس</h2>
                    <x-ui.phone-icons :contacts="$contacts" />
                </section>
            @endif

            @if (count($socialLinks) > 0)
                <section class="ps-card p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">شبکه‌های اجتماعی و وب‌سایت</h2>
                    <x-ui.social-icons :items="$socialLinks" />
                </section>
            @endif

            @if ($representation->nearby_railway_name || $representation->nearby_bus_name)
                <section class="ps-card p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">دسترسی عمومی</h2>
                    <dl class="space-y-3 text-sm">
                        @if ($representation->nearby_railway_name)
                            <div>
                                <dt class="text-ink-muted">ایستگاه راه‌آهن</dt>
                                <dd class="mt-0.5 font-medium text-ink">
                                    {{ $representation->nearby_railway_name }}
                                    @if ($representation->nearby_railway_distance)
                                        <span class="text-ink-muted">({{ number_format($representation->nearby_railway_distance, 1) }} کیلومتر)</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                        @if ($representation->nearby_bus_name)
                            <div>
                                <dt class="text-ink-muted">ایستگاه اتوبوس</dt>
                                <dd class="mt-0.5 font-medium text-ink">
                                    {{ $representation->nearby_bus_name }}
                                    @if ($representation->nearby_bus_distance)
                                        <span class="text-ink-muted">({{ number_format($representation->nearby_bus_distance, 1) }} کیلومتر)</span>
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </section>
            @endif
        </aside>
    </div>
@endsection
