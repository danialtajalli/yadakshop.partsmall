@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-site.breadcrumb :items="[
        ['label' => 'خانه', 'url' => route('home')],
        ['label' => 'جستجو', 'active' => true],
    ]" />

    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-linear-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <x-ui.section-heading
                label="جستجو"
                :title="$query ? 'نتایج جستجو برای '.$query : 'جستجو در پارتس‌مال'"
                description="جستجو در قطعات، فروشگاه‌ها، تعمیرگاه‌ها، نمایندگی‌ها و کاتالوگ خودرو"
                heading="h1"
            />
        </div>
    </div>

    @if ($query === '')
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">برای جستجو، نام قطعه، فروشگاه، تعمیرگاه، نمایندگی، کمپانی، خودرو یا مدل را وارد کنید.</p>
        </div>
    @elseif ($groups->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">نتیجه‌ای برای «{{ $query }}» یافت نشد.</p>
        </div>
    @else
        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="text-sm text-ink-muted">{{ number_format($total) }} نتیجه یافت شد</p>
        </div>

        <div class="space-y-8">
            @foreach ($groups as $group)
                <section>
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <h2 class="text-lg font-bold text-ink">{{ $group['label'] }}</h2>
                        <span class="text-xs font-medium text-ink-muted">{{ number_format($group['total']) }} نتیجه</span>
                    </div>

                    <div class="grid gap-2 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ($group['items'] as $item)
                            <a href="{{ $item['url'] }}" class="ps-card-interactive flex min-w-0 items-center gap-3 p-3">
                                <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-soft text-xs font-bold text-brand">
                                    {{ $item['type'] }}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-ink">{{ $item['title'] }}</span>
                                    @if ($item['subtitle'])
                                        <span class="mt-0.5 block truncate text-xs text-ink-muted">{{ $item['subtitle'] }}</span>
                                    @endif
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>
    @endif
@endsection
