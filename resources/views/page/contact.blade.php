@extends('layouts.app')

@section('title', $title)

@push('head')
    <style>
        .didar-contact-form__input--error {
            border-color: #f87171 !important;
            background-color: #fffafa !important;
        }

        .didar-contact-form__input--error:focus {
            border-color: #ef4444 !important;
            --tw-ring-color: rgb(239 68 68 / 0.25) !important;
        }

        .ps-contact-submit {
            user-select: none;
            transition: transform 120ms ease, background-color 120ms ease, box-shadow 120ms ease, opacity 120ms ease;
        }

        .ps-contact-submit:active,
        .ps-contact-submit.ps-touch-pressed {
            transform: scale(0.96);
            background-color: #222c3d;
            box-shadow: inset 0 2px 6px rgb(0 0 0 / 0.22);
        }

        .ps-contact-submit:disabled,
        .ps-contact-submit[aria-busy='true'] {
            cursor: wait;
            opacity: 0.85;
            transform: none;
            box-shadow: none;
        }

        @keyframes ps-contact-spin {
            to { transform: rotate(360deg); }
        }

        .ps-contact-submit__spinner {
            animation: ps-contact-spin 0.75s linear infinite;
        }
    </style>
@endpush

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
                @include('contact-form._form', ['formAction' => route('forms.contact.store')])
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

    <x-contact-form.status-modal />
@endsection
