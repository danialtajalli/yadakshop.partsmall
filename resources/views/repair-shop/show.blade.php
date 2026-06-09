@extends('layouts.app')

@section('title', $title)

@section('content')
    @php
        $resolveLinkUrl = static function (string $value): string {
            return str_starts_with($value, 'http') ? $value : '#';
        };
    @endphp

    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        @if ($repairShop->cover ?? null)
            <div class="h-40 w-full overflow-hidden border-b border-line sm:h-52">
                <img src="{{ $repairShop->cover }}" alt="" class="size-full object-cover">
            </div>
        @endif

        <div class="px-5 py-6 sm:px-8 sm:py-8">
            <x-site.breadcrumb :items="[
                ['label' => 'خانه', 'url' => url('/')],
                ['label' => 'تعمیرگاه‌ها', 'url' => route('repair-shops.index')],
                ['label' => $repairShop->name, 'active' => true],
            ]" />

            <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                <div class="flex size-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-gradient-to-br from-brand-soft to-accent-soft text-2xl font-bold text-brand-dark ring-1 ring-line sm:size-24">
                    @if ($repairShop->logo ?? null)
                        <img src="{{ $repairShop->logo }}" alt="{{ $repairShop->name }}" class="size-full object-cover">
                    @else
                        {{ mb_substr($repairShop->name, 0, 1) }}
                    @endif
                </div>

                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $repairShop->name }}</h1>

                    @if ($repairShop->work_description)
                        <p class="mt-1 text-sm text-ink-muted">{{ $repairShop->work_description }}</p>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                        @if ($repairShop->state)
                            <span class="text-ink-muted">{{ $repairShop->state->name }}</span>
                        @endif
                    </div>

                    @if ($repairShop->address)
                        <p class="mt-3 text-sm leading-7 text-ink-muted">{{ $repairShop->address }}</p>
                    @endif

                    @if ($repairShop->latitude && $repairShop->longitude)
                        <a
                            href="https://www.google.com/maps?q={{ $repairShop->latitude }},{{ $repairShop->longitude }}"
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
            @if ($repairShop->description)
                <section class="ps-card px-5 py-6 sm:px-6">
                    <x-ui.section-heading title="درباره تعمیرگاه" />
                    <x-ui.expandable-description id="repair-shop-description">
                        {!! $repairShop->description !!}
                    </x-ui.expandable-description>
                </section>
            @endif

            @if ($repairShop->repairCategories->isNotEmpty())
                <section>
                    <x-ui.section-heading
                        title="تخصص‌ها"
                        description="خدمات و حوزه‌های تعمیراتی این تعمیرگاه"
                    />

                    <ul class="flex flex-wrap gap-2">
                        @foreach ($repairShop->repairCategories as $category)
                            <li class="rounded-xl bg-brand-soft px-3 py-1.5 text-sm font-medium text-brand-dark">
                                {{ $category->name }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <aside class="space-y-6 lg:col-span-4">
            @if ($repairShop->phones->isNotEmpty())
                <section class="ps-card p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">تماس</h2>
                    <ul class="space-y-3">
                        @foreach ($repairShop->phones as $phone)
                            <li class="flex items-center justify-between gap-3 rounded-xl bg-surface px-3 py-2.5">
                                <span class="font-medium tabular-nums text-ink" dir="ltr">{{ $phone->phone_number }}</span>
                                <span class="text-xs text-ink-muted">{{ $phone->type->value }}</span>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            @if ($repairShop->responsible_person_name)
                <section class="ps-card p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">مسئول تعمیرگاه</h2>
                    <p class="text-sm font-medium text-ink">{{ $repairShop->responsible_person_name }}</p>
                </section>
            @endif

            @if ($repairShop->links->isNotEmpty())
                <section class="ps-card p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">شبکه‌های اجتماعی و وب‌سایت</h2>
                    <ul class="space-y-2">
                        @foreach ($repairShop->links as $link)
                            <li>
                                <a
                                    href="{{ $resolveLinkUrl($link->name) }}"
                                    target="_blank"
                                    rel="noopener"
                                    class="flex items-center justify-between gap-3 rounded-xl border border-line px-3 py-2.5 text-sm transition hover:border-brand/30 hover:bg-brand-soft/40"
                                >
                                    <span class="font-medium text-ink">{{ $link->link_type->value }}</span>
                                    <span class="truncate text-xs text-ink-muted" dir="ltr">{{ $link->name }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </aside>
    </div>
@endsection
