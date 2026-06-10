@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="px-5 py-6 sm:px-8 sm:py-8">
            <x-site.breadcrumb :items="[
                ['label' => 'خانه', 'url' => url('/')],
                ['label' => 'نمایندگی‌ها', 'url' => route('representations.index')],
                ['label' => $representation->name, 'active' => true],
            ]" />

            <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-brand-soft to-accent-soft text-2xl font-bold text-brand-dark ring-1 ring-line sm:size-24">
                    @if ($representation->logo_url ?? null)
                        <img src="{{ $representation->logo_url }}" alt="{{ $representation->name }}" class="size-full object-cover">
                    @else
                        {{ mb_substr($representation->name, 0, 1) }}
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $representation->name }}</h1>

                    @if ($representation->responsible_person_name)
                        <p class="mt-1 text-sm text-ink-muted">{{ $representation->responsible_person_name }}</p>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-ink-muted">
                        @if ($representation->company)
                            <span>{{ $representation->company->name }}</span>
                        @endif
                        @if ($representation->state)
                            <span>{{ $representation->state->name }}</span>
                        @endif
                        @if ($representation->city)
                            <span>{{ $representation->city->name }}</span>
                        @endif
                    </div>

                    @if ($representation->address)
                        <p class="mt-3 text-sm leading-7 text-ink-muted">{{ $representation->address }}</p>
                    @endif

                    @if ($representation->latitude && $representation->longitude)
                        <a
                            href="https://www.google.com/maps?q={{ $representation->latitude }},{{ $representation->longitude }}"
                            target="_blank"
                            rel="noopener"
                            class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-brand transition hover:text-brand-dark"
                        >
                            مشاهده روی نقشه
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                            </svg>
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-12">
        <div class="space-y-8 lg:col-span-8">
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
                        <div class="flex size-14 shrink-0 items-center justify-center overflow-hidden rounded-xl bg-surface ring-1 ring-line">
                            @if ($representation->company->logo_url ?? null)
                                <img src="{{ $representation->company->logo_url }}" alt="{{ $representation->company->name }}" class="size-full object-contain p-1.5">
                            @else
                                <span class="text-lg font-bold text-brand-dark">{{ mb_substr($representation->company->name, 0, 1) }}</span>
                            @endif
                        </div>
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
                    <ul class="space-y-3">
                        @foreach ($contacts as $contact)
                            <li class="flex items-center justify-between gap-3 rounded-xl bg-surface px-3 py-2.5">
                                <span class="font-medium tabular-nums text-ink" dir="ltr">{{ $contact['value'] }}</span>
                                <span class="text-xs text-ink-muted">{{ $contact['label'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if (count($socialLinks) > 0)
                <section class="ps-card p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">شبکه‌های اجتماعی و وب‌سایت</h2>
                    <ul class="space-y-2">
                        @foreach ($socialLinks as $link)
                            <li>
                                <a
                                    href="{{ $link['url'] }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex items-center justify-between gap-3 rounded-xl border border-line px-3 py-2.5 text-sm transition hover:border-brand/30 hover:bg-brand-soft/40"
                                >
                                    <span class="font-medium text-ink">{{ $link['label'] }}</span>
                                    <span class="truncate text-xs text-ink-muted" dir="ltr">{{ $link['url'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
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
