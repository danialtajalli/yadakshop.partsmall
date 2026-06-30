@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-site.breadcrumb :items="$breadcrumbs" />

    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <x-ui.section-heading
                :title="$page->title ?? $page->slug"
                heading="h1"
            />
        </div>
    </div>

    @if (filled($page->content))
        <article class="ps-card px-5 py-6 sm:px-8 sm:py-8">
            <div class="ps-prose max-w-none">
                {!! $page->content !!}
            </div>
        </article>
    @else
        <div class="rounded-2xl border border-dashed border-line bg-white px-6 py-12 text-center">
            <p class="text-sm text-ink-muted">محتوایی برای این صفحه ثبت نشده است.</p>
        </div>
    @endif
@endsection
