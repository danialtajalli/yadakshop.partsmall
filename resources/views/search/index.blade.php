@extends('layouts.app')

@section('title', $title)

@section('content')
    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <x-site.breadcrumb :items="[
                ['label' => 'خانه', 'url' => route('home')],
                ['label' => 'جستجو', 'active' => true],
            ]" />

            <x-ui.section-heading
                label="جستجو"
                :title="$query ? 'نتایج جستجو برای '.$query : 'جستجو در پارتس‌مال'"
                description="در حال حاضر جستجو روی قطعات انجام می‌شود و برای افزودن مدل‌های دیگر آماده است."
                heading="h1"
            />
        </div>
    </div>

    <x-home.parts-meilisearch-bar :action="route('search.index')" class="mb-8" />

    @if ($query === '')
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">برای جستجو، نام قطعه یا دسته‌بندی را وارد کنید.</p>
        </div>
    @elseif ($parts->isEmpty())
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">نتیجه‌ای برای «{{ $query }}» یافت نشد.</p>
        </div>
    @else
        <div class="mb-4 flex items-center justify-between gap-3">
            <p class="text-sm text-ink-muted">{{ number_format($parts->total()) }} قطعه یافت شد</p>
        </div>

        <div class="grid gap-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-4 2xl:grid-cols-4">
            @foreach ($parts as $part)
                <x-ui.part-card :part="$part" :url="route('part.show', $part->slug)" />
            @endforeach
        </div>

        <div class="mt-10">
            {{ $parts->links() }}
        </div>
    @endif
@endsection
