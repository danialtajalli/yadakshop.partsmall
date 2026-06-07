@extends('layouts.app')

@section('title', $title)

@section('content')
    {{-- Navigation --}}
    <nav class="mb-8 flex flex-wrap items-center gap-2 text-sm text-stone-500" aria-label="مسیر">
        <a href="{{ url('/') }}" class="font-medium text-amber-700 transition hover:text-amber-800">خانه</a>
        <span class="text-stone-300">/</span>
        <span class="font-medium text-stone-700">{{ $company->name }}</span>
        <span class="text-stone-300">/</span>
        <span>{{ $car->name }}</span>
        <span class="text-stone-300">/</span>
        <span class="text-stone-800">{{ $model->name }}</span>
    </nav>

    {{-- Repair descriptions --}}
    <section class="mb-10">
        <h2 class="mb-4 text-xl font-bold text-stone-800">شرح تعمیرات و هزینه</h2>

        @if (count($repairCards) > 0)
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($repairCards as $card)
                    <article class="rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                        <h3 class="mb-2 font-semibold text-stone-800">{{ $card['type'] }}</h3>
                        @if ($card['wage_name'] && $card['wage_name'] !== $card['type'])
                            <p class="mb-3 text-sm text-stone-500">{{ $card['wage_name'] }}</p>
                        @endif
                        <p class="text-sm text-stone-600">هزینه تقریبی تعمیر</p>
                        <p class="mt-1 text-2xl font-bold text-amber-700">
                            @if ($card['cost'] !== null)
                                {{ number_format($card['cost']) }}
                                <span class="text-base font-normal text-stone-500">تومان</span>
                            @else
                                <span class="text-base font-normal text-stone-400">نامشخص</span>
                            @endif
                        </p>
                    </article>
                @endforeach
            </div>
        @else
            <p class="rounded-xl border border-dashed border-stone-300 bg-white p-6 text-center text-stone-500">
                اطلاعات تعمیر برای این قطعه ثبت نشده است.
            </p>
        @endif
    </section>

    {{-- Shops --}}
    <section class="mb-10">
        <h2 class="mb-4 text-xl font-bold text-stone-800">فروشگاه‌های مرتبط</h2>

        @if ($shops->isNotEmpty())
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($shops as $shop)
                    <article class="flex flex-col rounded-xl border border-stone-200 bg-white p-5 shadow-sm">
                        <div class="mb-4 flex items-start gap-4">
                            <div class="flex size-14 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-amber-100 to-amber-200 text-lg font-bold text-amber-800">
                                {{ mb_substr($shop->name, 0, 1) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="truncate font-semibold text-stone-800">{{ $shop->name }}</h3>
                                @if ($shop->secondary_name)
                                    <p class="truncate text-sm text-stone-500">{{ $shop->secondary_name }}</p>
                                @endif
                                <div class="mt-1 flex items-center gap-1 text-sm text-amber-600">
                                    @if ($shop->average_rating)
                                        <span aria-hidden="true">★</span>
                                        <span>{{ number_format($shop->average_rating, 1) }}</span>
                                    @else
                                        <span class="text-stone-400">بدون امتیاز</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto flex gap-2">
                            <a
                                href="#"
                                class="flex-1 rounded-lg bg-amber-600 px-3 py-2 text-center text-sm font-medium text-white transition hover:bg-amber-700"
                            >
                                مشاهده فروشگاه
                            </a>
                            <button
                                type="button"
                                class="rounded-lg border border-stone-300 px-3 py-2 text-sm font-medium text-stone-700 transition hover:bg-stone-50"
                                onclick="document.getElementById('shop-modal-{{ $shop->id }}').showModal()"
                            >
                                اطلاعات
                            </button>
                        </div>

                        <dialog
                            id="shop-modal-{{ $shop->id }}"
                            class="w-full max-w-md rounded-xl border border-stone-200 bg-white p-0 shadow-xl backdrop:bg-stone-900/50"
                        >
                            <div class="border-b border-stone-100 px-5 py-4">
                                <div class="flex items-center justify-between gap-3">
                                    <h4 class="font-bold text-stone-800">{{ $shop->name }}</h4>
                                    <button
                                        type="button"
                                        class="rounded-lg p-1 text-stone-400 transition hover:bg-stone-100 hover:text-stone-600"
                                        onclick="document.getElementById('shop-modal-{{ $shop->id }}').close()"
                                        aria-label="بستن"
                                    >
                                        ✕
                                    </button>
                                </div>
                            </div>
                            <div class="max-h-[70vh] space-y-4 overflow-y-auto px-5 py-4 text-sm">
                                @if ($shop->description)
                                    <p class="leading-relaxed text-stone-600">{{ $shop->description }}</p>
                                @endif

                                @if ($shop->address)
                                    <div>
                                        <p class="mb-1 font-semibold text-stone-700">آدرس</p>
                                        <p class="text-stone-600">{{ $shop->address }}</p>
                                    </div>
                                @endif

                                @if ($shop->phones->isNotEmpty())
                                    <div>
                                        <p class="mb-1 font-semibold text-stone-700">تلفن‌ها</p>
                                        <ul class="space-y-1">
                                            @foreach ($shop->phones as $phone)
                                                <li class="flex justify-between gap-2 text-stone-600">
                                                    <span>{{ $phone->phone_number }}</span>
                                                    <span class="text-xs text-stone-400">{{ $phone->type->value }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if ($shop->links->isNotEmpty())
                                    <div>
                                        <p class="mb-1 font-semibold text-stone-700">شبکه‌های اجتماعی و وب</p>
                                        <ul class="space-y-1">
                                            @foreach ($shop->links as $link)
                                                <li>
                                                    <a
                                                        href="{{ str_starts_with($link->name, 'http') ? $link->name : '#' }}"
                                                        class="text-amber-700 hover:underline"
                                                        target="_blank"
                                                        rel="noopener"
                                                    >
                                                        {{ $link->link_type->value }}
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if ($shop->open_time && $shop->close_time)
                                    <div>
                                        <p class="mb-1 font-semibold text-stone-700">ساعات کاری</p>
                                        <p class="text-stone-600">{{ $shop->open_time }} – {{ $shop->close_time }}</p>
                                    </div>
                                @endif
                            </div>
                        </dialog>
                    </article>
                @endforeach
            </div>
        @else
            <p class="rounded-xl border border-dashed border-stone-300 bg-white p-6 text-center text-stone-500">
                فروشگاهی برای این قطعه یافت نشد.
            </p>
        @endif
    </section>

    {{-- Part description --}}
    <section>
        <h2 class="mb-4 text-xl font-bold text-stone-800">مشخصات قطعه</h2>

        <div class="rounded-xl border border-stone-200 bg-white shadow-sm">
            <div class="border-b border-stone-100 px-5 py-4">
                <h3 class="text-lg font-bold text-stone-800">{{ $part->name }}</h3>
                @if ($part->partsCategory)
                    <span class="mt-2 inline-block rounded-full bg-amber-100 px-3 py-0.5 text-xs font-medium text-amber-800">
                        {{ $part->partsCategory->name }}
                    </span>
                @endif
            </div>

            <dl class="grid gap-4 px-5 py-4 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-stone-500">نام خودرو</dt>
                    <dd class="mt-0.5 font-semibold text-stone-800">{{ $car->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-stone-500">نام قطعه</dt>
                    <dd class="mt-0.5 font-semibold text-stone-800">{{ $part->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-stone-500">مدل خودرو</dt>
                    <dd class="mt-0.5 font-semibold text-stone-800">{{ $model->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-stone-500">شرکت سازنده</dt>
                    <dd class="mt-0.5 font-semibold text-stone-800">{{ $company->name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-stone-500">کشور سازنده</dt>
                    <dd class="mt-0.5 font-semibold text-stone-800">{{ $company->country ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-stone-500">اسلاگ خودرو</dt>
                    <dd class="mt-0.5 font-mono text-sm text-stone-700">{{ $car->slug }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-stone-500">اسلاگ قطعه</dt>
                    <dd class="mt-0.5 font-mono text-sm text-stone-700">{{ $part->slug }}</dd>
                </div>
            </dl>

            @if ($part->description)
                    <div class="border-t border-stone-100 px-5 py-4">
                        <p class="mb-2 text-sm font-medium text-stone-500">توضیحات</p>
                        <div class="space-y-3 text-sm leading-relaxed text-stone-600 [&_h3]:font-semibold [&_h3]:text-stone-800 [&_p]:mb-2 [&_ul]:list-disc [&_ul]:pr-5">
                            {!! $part->description !!}
                        </div>
                    </div>
            @endif
        </div>
    </section>
@endsection
