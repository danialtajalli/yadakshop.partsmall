@extends('layouts.app')

@section('title', $title)

@section('content')
    {{-- Hero + repair cards beside CTA sidebar --}}
    <div class="mb-12 lg:grid lg:grid-cols-12 lg:items-start lg:gap-8">
        <div class="space-y-8 lg:col-span-8">
            <div class="overflow-hidden rounded-2xl border border-line bg-white shadow-card">
                <div class="border-b border-line bg-gradient-to-l from-brand-soft via-white to-white px-5 py-6 sm:px-8 sm:py-8">
                    <x-site.breadcrumb :items="[
                        ['label' => 'خانه', 'url' => url('/')],
                        ['label' => $company->name, 'emphasized' => true],
                        ['label' => $car->name . ' ' . $model->name, 'active' => true, 'url' => route('car.parts', ['company' => $company->slug, 'car' => $car->slug, 'model' => $model->slug])],
                    ]" />

                    @if ($shops->isNotEmpty())
                        <a
                            href="#shops"
                            data-shops-jump
                            class="ps-shops-jump mb-5 flex items-center justify-between gap-3 rounded-xl border border-brand/25 bg-brand-soft px-4 py-3.5 text-sm transition hover:border-brand/40 hover:bg-brand-soft/80 active:scale-[0.99] lg:hidden"
                        >
                            <span class="flex min-w-0 items-center gap-2.5 font-medium text-ink">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-lg bg-brand text-white">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36a1.125 1.125 0 0 1-1.009-.69L.5 9.75A1.125 1.125 0 0 1 1.509 8.5H5.25m8.25 0V5.625A2.625 2.625 0 0 0 11.625 3h-3.75A2.625 2.625 0 0 0 5.25 5.625V8.5m8.25 0H5.25" />
                                    </svg>
                                </span>
                                <span>
                                    <span class="block font-semibold text-brand-dark">{{ $shops->count() }} فروشگاه مرتبط</span>
                                    <span class="block text-xs text-ink-muted">برای خرید {{ $part->name }} کلیک کنید</span>
                                </span>
                            </span>
                            <span class="flex shrink-0 items-center gap-1 text-xs font-semibold text-brand">
                                مشاهده
                                <svg class="ps-shops-jump-chevron size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                                </svg>
                            </span>
                        </a>
                    @endif

                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div>
                            @if ($part->partsCategory)
                                <span class="mb-2 inline-flex items-center rounded-full bg-brand-soft px-3 py-1 text-xs font-medium text-brand-dark">
                                    {{ $part->partsCategory->name }}
                                </span>
                            @endif
                            <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $part->name . ' ' . $company->name . ' ' . $car->name . ' ' . $model->name }}</h1>
                        </div>
                    </div>
                </div>
            </div>
            <section>

            @if ($repairLocator)
                <div class="mt-6 mb-6">
                    <x-product.repair-locator :repair-locator="$repairLocator" />
                </div>
            @endif

            <x-ui.section-heading
                label="تعمیرات"
                title="شرح تعمیرات و هزینه"
                description="برآورد هزینه اجرت بر اساس نوع تعمیر"
            />

            @if (count($repairCards) > 0)
                <div class="grid gap-5 sm:grid-cols-2">
                    @foreach ($repairCards as $card)
                        <article class="ps-card-interactive group relative overflow-hidden p-6">
                            <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-l from-brand to-brand-dark opacity-80 transition group-hover:opacity-100"></div>
                            <div class="mb-4 flex size-10 items-center justify-center rounded-xl bg-brand-soft">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.88m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336 4.5 4.5 0 0 0-6.336-4.486c-.072 1.172-.088 2.402.14 3.743Z" />
                                </svg>
                            </div>
                            <h3 class="font-semibold text-ink">اجرت {{ $card['type'] }}</h3>
                            @if ($card['wage_name'] && $card['wage_name'] !== $card['type'])
                                <p class="mt-1 text-sm text-ink-muted">{{ $card['wage_name'] }}</p>
                            @endif
                            <div class="mt-5 border-t border-line pt-4">
                                <p class="text-xs font-medium text-ink-muted">هزینه تقریبی</p>
                                @if ($card['cost'] !== null)
                                    <p class="mt-0.5 text-2xl font-bold tabular-nums text-accent">
                                        {{ number_format($card['cost']) }}
                                        <span class="text-sm font-medium text-ink-muted">تومان</span>
                                    </p>
                                @else
                                    <p class="mt-0.5 text-base text-ink-muted">نامشخص</p>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
                    <p class="text-sm text-ink-muted">اطلاعات تعمیر برای این قطعه ثبت نشده است.</p>
                </div>
            @endif
            </section>
        </div>

        <div class="mt-6 lg:col-span-4 lg:mt-0">
            <div class="lg:sticky lg:top-24">
                <x-site.cta-sidebar
                    :telegram-title="'به گروه تلگرام ' . $company->name . ' ' . $car->name . ' سواران بپیوندید'"
                    :telegram-url="'https://t.me/' . $company->slug . '_saravan_partsmall'"
                />
            </div>
        </div>
    </div>

    {{-- Shops --}}
    <section id="shops" class="mb-12 scroll-mt-20 ps-shops-section">
        <x-ui.section-heading
            label="فروشندگان"
            title="فروشگاه‌های مرتبط"
            description="فروشگاه‌هایی که این قطعه یا دسته آن را عرضه می‌کنند"
        />

        @if ($shops->isNotEmpty())
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($shops as $shop)
                    <article class="ps-card-interactive relative flex flex-col p-5">
                        <div class="mb-5 flex items-start gap-4">
                            <div class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-brand-soft to-accent-soft text-lg font-bold text-brand-dark ring-1 ring-line">
                                @if ($shop->logo)
                                    <img src="{{ $shop->logo }}" alt="{{ $shop->name }}" class="w-full h-full object-cover" />
                                @else
                                    {{ mb_substr($shop->name, 0, 1) }}
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate font-semibold text-ink">{{ $shop->name }}</h3>
                                @if ($shop->secondary_name)
                                    <p class="truncate text-sm text-ink-muted">{{ $shop->secondary_name }}</p>
                                @endif
                                <div class="mt-2 inline-flex items-center gap-1 rounded-lg bg-accent-soft px-2 py-0.5 text-xs font-medium text-accent">
                                    @if ($shop->average_rating)
                                        <svg class="size-3.5 fill-current" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z"/></svg>
                                        <span>{{ number_format($shop->average_rating, 1) }}</span>
                                    @else
                                        <span class="text-ink-muted">بدون امتیاز</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto flex gap-2.5">
                            <a href="{{ route('shop.profile', $shop->slug) }}" class="ps-btn-primary relative z-20 flex-1 text-center">مشاهده</a>
                            <button
                                type="button"
                                class="ps-btn-secondary shrink-0"
                                onclick="document.getElementById('shop-modal-{{ $shop->id }}').showModal()"
                            >
                                اطلاعات
                            </button>
                        </div>

                        <dialog
                            id="shop-modal-{{ $shop->id }}"
                            class="fixed inset-0 z-50 m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl border border-line bg-white p-0 shadow-2xl backdrop:bg-ink/40 open:animate-none"
                        >
                            <div class="flex items-center justify-between gap-3 border-b border-line px-5 py-4">
                                <h4 class="font-bold text-ink">{{ $shop->name }}</h4>
                                <button
                                    type="button"
                                    class="flex size-8 items-center justify-center rounded-lg text-ink-muted transition hover:bg-surface hover:text-ink"
                                    onclick="document.getElementById('shop-modal-{{ $shop->id }}').close()"
                                    aria-label="بستن"
                                >
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                            <div class="max-h-[70vh] space-y-5 overflow-y-auto px-5 py-5 text-sm">
                                @if ($shop->description)
                                <div class="border-t border-line px-5 py-6 sm:px-6">
                                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-brand">معرفی {{ $part->name }} {{ $company->name }} {{ $car->name }}</p>
                                        {!! $shop->description !!}
                                </div>
                                @endif

                                @if ($shop->address)
                                    <div class="rounded-xl bg-surface p-4">
                                        <p class="mb-1 text-xs font-semibold text-ink">آدرس</p>
                                        <p class="text-ink-muted">{{ $shop->address }}</p>
                                    </div>
                                @endif

                                @if ($shop->phones->isNotEmpty())
                                    <div>
                                        <p class="mb-2 text-xs font-semibold text-ink">تلفن‌ها</p>
                                        <x-ui.phone-icons :phones="$shop->phones" />
                                    </div>
                                @endif

                                @if ($shop->links->isNotEmpty())
                                    <div>
                                        <p class="mb-2 text-xs font-semibold text-ink">شبکه‌های اجتماعی</p>
                                        @php
                                            $resolveShopLinkUrl = static fn (string $value): string => str_starts_with($value, 'http') ? $value : '#';
                                        @endphp
                                        <x-ui.social-icons :links="$shop->links" :resolve-url="$resolveShopLinkUrl" />
                                    </div>
                                @endif

                                @if ($shop->open_time && $shop->close_time)
                                    <div class="rounded-xl bg-surface p-4">
                                        <p class="mb-1 text-xs font-semibold text-ink">ساعات کاری</p>
                                        <p class="tabular-nums text-ink-muted" dir="ltr">{{ $shop->open_time }} – {{ $shop->close_time }}</p>
                                    </div>
                                @endif
                            </div>
                        </dialog>
                    </article>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
                <p class="text-sm text-ink-muted">فروشگاهی برای این قطعه یافت نشد.</p>
            </div>
        @endif
    </section>

    {{-- Part specs --}}
    <section>
        <x-ui.section-heading label="جزئیات" title="مشخصات قطعه" />

        <div class="ps-card overflow-hidden">
            <dl class="divide-y divide-line">
                @foreach ([
                    'نام خودرو' => $car->name,
                    'نام قطعه' => $part->name,
                    'مدل خودرو' => $model->name,
                    'شرکت سازنده' => $company->name,
                    'کشور سازنده' => $company->country ?? '—',
                    'نام لاتین خودرو' => $car->slug,
                    'نام لاتین قطعه' => $part->slug,
                ] as $label => $value)
                    <div class="grid gap-1 px-5 py-4 sm:grid-cols-3 sm:gap-4 sm:px-6 even:bg-surface/50">
                        <dt class="text-sm font-medium text-ink-muted">{{ $label }}</dt>
                        <dd @class([
                            'text-sm font-semibold text-ink sm:col-span-2',
                            'font-mono font-normal text-ink-muted' => str_contains($label, 'اسلاگ'),
                        ])>{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>

            @if ($part->description)
                <div class="border-t border-line px-5 py-6 sm:px-6">
                    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-brand">معرفی {{ $part->name }} {{ $company->name }} {{ $car->name }}</h2>
                    <x-ui.expandable-description id="part-description">
                        {!! $part->description !!}
                    </x-ui.expandable-description>
                </div>
            @endif

            @if ($car->description)
                <div class="border-t border-line px-5 py-6 sm:px-6">
                    <h2 class="mb-3 text-xs font-semibold uppercase tracking-wider text-brand">معرفی خودرو {{ $company->name }} {{ $car->name }}</h2>
                    <x-ui.expandable-description id="car-description">
                        {!! $car->description !!}
                    </x-ui.expandable-description>
                </div>
            @endif
        </div>
    </section>

    @if ($shops->isNotEmpty())
        @push('scripts')
            <script>
                (function () {
                    const link = document.querySelector('[data-shops-jump]');
                    const target = document.getElementById('shops');

                    if (!link || !target) {
                        return;
                    }

                    const headerOffset = 80;
                    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

                    link.addEventListener('click', function (event) {
                        event.preventDefault();

                        link.classList.add('is-scrolling');

                        const top = target.getBoundingClientRect().top + window.scrollY - headerOffset;

                        const finish = function () {
                            link.classList.remove('is-scrolling');
                            target.classList.add('ps-shops-section--highlight');
                            window.setTimeout(function () {
                                target.classList.remove('ps-shops-section--highlight');
                            }, 900);
                        };

                        if (reducedMotion) {
                            window.scrollTo(0, top);
                            finish();

                            return;
                        }

                        window.scrollTo({ top: top, behavior: 'smooth' });

                        if ('onscrollend' in window) {
                            window.addEventListener('scrollend', finish, { once: true });
                        } else {
                            window.setTimeout(finish, 750);
                        }
                    });
                })();
            </script>
        @endpush
    @endif
@endsection
