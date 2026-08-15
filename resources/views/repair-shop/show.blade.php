@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-site.breadcrumb :items="[
        ['label' => 'خانه', 'url' => url('/')],
        ['label' => 'تعمیرگاه‌ها', 'url' => route('repair-shops.index')],
        ['label' => $repairShop->name, 'active' => true],
    ]" />

    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        @if ($repairShop->cover ?? null)
            <div class="h-40 w-full overflow-hidden border-b border-line sm:h-52">
                <img src="{{ $repairShop->cover }}" alt="تصویر تعمیرگاه {{ $repairShop->name }}" class="size-full object-cover">
            </div>
        @endif

        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                <x-ui.company-logo
                    :name="$repairShop->name"
                    :logo-url="$repairShop->logo ?? null"
                    alt="لوگوی تعمیرگاه {{ $repairShop->name }}"
                    size="xl"
                />

                <div class="min-w-0 flex-1">
                    <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $repairShop->name }}</h1>

                    @if ($repairShop->work_description)
                        <p class="mt-1 text-sm text-ink-muted">{{ $repairShop->work_description }}</p>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                        @if ($repairShop->city?->state)
                            <span class="text-ink-muted">{{ $repairShop->city->state->name }}</span>
                        @endif
                    </div>

                    @if ($repairShop->address)
                        <p class="mt-3 text-sm leading-7 text-ink-muted">{{ $repairShop->address }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-12">
        <div class="space-y-8 lg:col-span-8">
            @if ($repairShop->latitude && $repairShop->longitude)
                <x-ui.location-map
                    :latitude="$repairShop->latitude"
                    :longitude="$repairShop->longitude"
                    :title="$repairShop->name"
                    :address="$repairShop->address"
                />
            @endif

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
                    <x-ui.phone-icons :phones="$repairShop->phones" />
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
                    <x-ui.social-icons :links="$repairShop->links" />
                </section>
            @endif
        </aside>
    </div>
@endsection
