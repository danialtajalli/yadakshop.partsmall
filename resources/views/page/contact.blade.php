@extends('layouts.app')

@section('title', $title)

@section('content')
    <x-site.breadcrumb :items="$breadcrumbs" />

    <div class="mb-8 overflow-hidden rounded-2xl border border-line bg-white shadow-card">
        <div class="border-b border-line bg-gradient-to-l from-gray-100 via-white px-5 py-6 sm:px-8 sm:py-8">
            <x-ui.section-heading
                :title="$page->title ?? 'تماس با ما'"
                heading="h1"
            />

            <p class="mt-4 max-w-3xl text-sm leading-7 text-ink-muted">
                تیم {{ config('app.name', 'پارتس‌مال') }} همواره آماده پاسخگویی به سوالات، راهنمایی و پشتیبانی شماست.
                اگر نیاز به اطلاعات بیشتر یا پیگیری درخواست دارید، از راه‌های زیر با ما در ارتباط باشید.
            </p>
        </div>
    </div>

    <div class="grid min-w-0 gap-8 lg:grid-cols-2 lg:items-start">
        <div class="min-w-0 space-y-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="ps-card flex min-w-0 gap-4 p-5">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-brand text-white shadow-card">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-ink">آدرس</h2>
                        <p class="mt-1 text-sm leading-6 text-ink-muted">{{ $contact['address'] }}</p>
                    </div>
                </div>

                <div class="ps-card flex min-w-0 gap-4 p-5">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-brand text-white shadow-card">
                        <i class="fa-solid fa-clock" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-ink">ساعت کاری</h2>
                        <p class="mt-1 text-sm leading-6 text-ink-muted">{{ $contact['hours'] }}</p>
                    </div>
                </div>

                <div class="ps-card flex min-w-0 gap-4 p-5">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-brand text-white shadow-card">
                        <i class="fa-solid fa-phone" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-ink">تلفن</h2>
                        <a dir="rtl" href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}" class="mt-1 block text-sm text-brand transition hover:text-brand-dark text-right">
                            <span dir="ltr">
                                {{ $contact['phone'] }}
                            </span>
                        </a>
                    </div>
                </div>

                <div class="ps-card flex min-w-0 gap-4 p-5">
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-brand text-white shadow-card">
                        <i class="fa-solid fa-mobile-screen-button" aria-hidden="true"></i>
                    </div>
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-ink">ایمیل</h2>
                        <a href="mailto:{{ $contact['email'] }}" class="mt-1 block text-sm text-brand transition hover:text-brand-dark">
                            {{ $contact['email'] }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="ps-card min-w-0 p-4 sm:p-5">
                <h2 class="mb-4 text-base font-bold text-ink">فرم تماس</h2>
                <iframe
                    id="contact-form-iframe"
                    src="{{ asset('forms/contact.php?embed=1') }}"
                    title="فرم تماس"
                    class="block w-full border-0"
                    style="min-height: 28rem; height: 28rem;"
                    scrolling="no"
                    loading="lazy"
                ></iframe>
            </div>

            {{-- @if (filled($page->content))
                <div class="ps-card min-w-0 overflow-hidden p-4 sm:p-5">
                    <div class="prose prose-sm max-w-none text-ink-muted">
                        {!! $page->content !!}
                    </div>
                </div>
            @endif --}}
        </div>

        <div class="overflow-hidden rounded-2xl border border-line bg-white shadow-card lg:sticky lg:top-24">
            <img
                src="{{ $contact['image_url'] }}"
                alt="تماس با {{ config('app.name', 'پارتس‌مال') }}"
                class="block w-full object-cover"
                loading="lazy"
            >
        </div>
    </div>

    @once
        @push('scripts')
            <script>
                window.addEventListener('message', function (event) {
                    if (event.origin !== window.location.origin) {
                        return;
                    }

                    if (!event.data || event.data.type !== 'didar-contact-form-resize') {
                        return;
                    }

                    const iframe = document.getElementById('contact-form-iframe');

                    if (!iframe || typeof event.data.height !== 'number') {
                        return;
                    }

                    iframe.style.height = Math.max(event.data.height, 448) + 'px';
                });
            </script>
        @endpush
    @endonce
@endsection
