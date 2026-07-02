@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-site.breadcrumb :items="[
        ['label' => 'خانه', 'url' => url('/')],
        ['label' => 'فروشگاه‌ها', 'url' => route('shops.index')],
        ['label' => $shop->name, 'active' => true],
    ]" />

    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        @if ($shop->cover ?? null)
            <div class="h-40 w-full overflow-hidden border-b border-line sm:h-52">
                <img src="{{ $shop->cover }}" alt="" class="size-full object-cover">
            </div>
        @endif

        <div class="px-5 py-6 sm:px-8 sm:py-8 bg-gradient-to-l from-gray-100 via-white">
            <div class="flex flex-col gap-6 sm:flex-row sm:items-start">
                <x-ui.company-logo
                    :name="$shop->name"
                    :logo-url="$shop->logo ?? null"
                    size="xl"
                />

                <div class="min-w-0 flex-1">
                    <div class="flex min-w-0 flex-wrap items-center gap-2">
                        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $shop->name }}</h1>
                        @if ($shop->verified ?? false)
                            <x-shop.trusted-badge />
                        @endif
                    </div>

                    @if ($shop->secondary_name)
                        <p class="mt-1 text-sm text-ink-muted">{{ $shop->secondary_name }}</p>
                    @endif

                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                        @if ($averageRating)
                            <span class="inline-flex items-center gap-1 rounded-lg bg-accent-soft px-2.5 py-1 font-medium text-accent">
                                <svg class="size-4 fill-current" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z"/></svg>
                                {{ number_format($averageRating, 1) }}
                            </span>
                        @endif

                        @if ($commentsCount > 0)
                            <span class="text-ink-muted">{{ number_format($commentsCount) }} نظر</span>
                        @endif

                        @if ($shop->state)
                            <span class="text-ink-muted">{{ $shop->state->name }}</span>
                        @endif
                    </div>

                    @if ($shop->address)
                        <p class="mt-3 text-sm leading-7 text-ink-muted">{{ $shop->address }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-8 lg:grid-cols-12">
        <div class="space-y-8 lg:col-span-8">
            @if ($shop->latitude && $shop->longitude)
                <x-ui.location-map
                    :latitude="$shop->latitude"
                    :longitude="$shop->longitude"
                    :title="$shop->name"
                    :address="$shop->address"
                />
            @endif

            @if ($shop->description)
                <section class="ps-card px-5 py-6 sm:px-6">
                    <x-ui.section-heading title="درباره فروشگاه" />
                    <x-ui.expandable-description id="shop-description">
                        {!! $shop->description !!}
                    </x-ui.expandable-description>
                </section>
            @endif

            @if ($shop->companies->isNotEmpty())
                <section>
                    <x-ui.section-heading
                        title="برندها و شرکت‌های مرتبط"
                        description="خودروسازان و برندهایی که این فروشگاه پوشش می‌دهد"
                    />

                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach ($shop->companies as $company)
                            <article class="ps-card flex items-center gap-4 p-4">
                                <x-ui.company-logo
                                    :name="$company->name"
                                    :logo-url="$company->logo_url ?? null"
                                    size="listing"
                                />
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-ink">{{ $company->name }}</h3>
                                    @if ($company->country)
                                        <p class="mt-0.5 text-sm text-ink-muted">{{ $company->country }}</p>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($shop->partsCategories->isNotEmpty())
                <section>
                    <x-ui.section-heading title="دسته‌بندی قطعات" />
                    <ul class="flex flex-wrap gap-2">
                        @foreach ($shop->partsCategories as $category)
                            <li class="rounded-xl bg-gray-100 px-3 py-1.5 text-sm font-medium text-brand-dark">
                                {{ $category->name }}
                            </li>
                        @endforeach
                    </ul>
                </section>
            @endif

            <x-shop.comments-section
                :shop="$shop"
                :comments="$shop->comments"
                :comments-count="$commentsCount"
                :average-rating="$averageRating"
            />
        </div>

        <aside class="space-y-6 lg:col-span-4">
            @if ($shop->phones->isNotEmpty())
                <section class="ps-card p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">تماس</h2>
                    <x-ui.phone-icons :phones="$shop->phones" />
                </section>
            @endif

            @if ($shop->person_responsible_name || $shop->person_responsible_email)
                <section class="ps-card p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">مسئول فروشگاه</h2>
                    <dl class="space-y-3 text-sm">
                        @if ($shop->person_responsible_name)
                            <div>
                                <dt class="text-ink-muted">نام</dt>
                                <dd class="mt-0.5 font-medium text-ink">{{ $shop->person_responsible_name }}</dd>
                            </div>
                        @endif
                        @if ($shop->person_responsible_email)
                            <div>
                                <dt class="text-ink-muted">ایمیل</dt>
                                <dd class="mt-0.5">
                                    <a href="mailto:{{ $shop->person_responsible_email }}" class="font-medium text-brand hover:text-brand-dark" dir="ltr">
                                        {{ $shop->person_responsible_email }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </section>
            @endif

            @if ($shop->links->isNotEmpty())
                <section class="ps-card p-5">
                    <h2 class="mb-4 text-base font-bold text-ink">شبکه‌های اجتماعی و وب سایت</h2>
                    @if ($shop->website_show !== null)
                        <x-ui.social-icons :links="[$shop->website_show]" />
                    @endif
                    <x-ui.social-icons :links="$shop->links" />
                </section>
            @endif

            <section class="ps-card p-5">
                <h2 class="mb-4 text-base font-bold text-ink">ساعات کاری</h2>
                <dl class="space-y-3 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-ink-muted">شنبه تا چهارشنبه</dt>
                        <dd class="tabular-nums font-medium text-ink" dir="ltr">{{ $shop->open_time }} – {{ $shop->close_time }}</dd>
                    </div>
                    @if ($shop->open_time_thursday && $shop->close_time_thursday)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-ink-muted">پنج‌شنبه</dt>
                            <dd class="tabular-nums font-medium text-ink" dir="ltr">{{ $shop->open_time_thursday }} – {{ $shop->close_time_thursday }}</dd>
                        </div>
                    @endif
                    @if ($shop->open_time_friday && $shop->close_time_friday)
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-ink-muted">جمعه</dt>
                            <dd class="tabular-nums font-medium text-ink" dir="ltr">{{ $shop->open_time_friday }} – {{ $shop->close_time_friday }}</dd>
                        </div>
                    @endif
                </dl>
            </section>
        </aside>
    </div>
@endsection
