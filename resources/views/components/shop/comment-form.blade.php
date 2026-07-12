@props([
    'shop',
])

@php
    $inputClass = 'w-full rounded-xl border border-line bg-white px-3.5 py-2.5 text-sm text-ink outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20';
    $errorClass = 'ps-input--error';
@endphp

<form
    method="post"
    action="{{ route('shop.comments.store', $shop->slug) }}"
    class="ps-card mb-6 p-5 sm:p-6"
    data-shop-comment-form
>
    @csrf

    <h3 class="text-base font-bold text-ink">ثبت نظر شما</h3>
    <p class="mt-1 text-sm text-ink-muted">نظر شما پس از بررسی در این صفحه نمایش داده می‌شود.</p>

    <x-ui.form-errors class="mt-5" />

    <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
        <label for="company_url">Company URL</label>
        <input type="text" name="company_url" id="company_url" value="" tabindex="-1" autocomplete="off">
    </div>

    <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <div>
            <label for="comment-fullname" class="mb-1.5 block text-xs font-medium text-ink-muted">نام و نام خانوادگی</label>
            <input
                type="text"
                name="fullname"
                id="comment-fullname"
                value="{{ old('fullname') }}"
                required
                autocomplete="name"
                class="{{ $inputClass }} @error('fullname') {{ $errorClass }} @enderror"
            >
        </div>

        <div>
            <label for="comment-mobile" class="mb-1.5 block text-xs font-medium text-ink-muted">شماره موبایل (اختیاری)</label>
            <input
                type="tel"
                name="mobile"
                id="comment-mobile"
                value="{{ old('mobile') }}"
                autocomplete="tel"
                dir="ltr"
                class="{{ $inputClass }} @error('mobile') {{ $errorClass }} @enderror"
            >
        </div>
    </div>

    <div class="mt-4">
        <span class="mb-1.5 block text-xs font-medium text-ink-muted">امتیاز شما</span>
        <div
            class="ps-comment-star-picker flex flex-wrap items-center @error('rating') ps-comment-star-picker--error @enderror"
            data-rating-picker
        >
            <input type="hidden" name="rating" value="{{ old('rating') }}" data-rating-input required>
            @for ($star = 1; $star <= 5; $star++)
                <button
                    type="button"
                    data-rating-star="{{ $star }}"
                    data-star-active="false"
                    aria-label="امتیاز {{ $star }} از ۵"
                    aria-pressed="false"
                >
                    <p class="text-xs">{{ $star }}</p>
                    <svg viewBox="0 0 20 20" aria-hidden="true" data-star-icon>
                        <path fill="#e2e8f0" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z" />
                    </svg>
                </button>
            @endfor
            <span class="ms-2 text-xs text-ink-muted" data-rating-label>یک امتیاز انتخاب کنید</span>
        </div>
    </div>

    <div class="mt-4">
        <label for="comment-body" class="mb-1.5 block text-xs font-medium text-ink-muted">متن نظر</label>
        <textarea
            name="body"
            id="comment-body"
            rows="4"
            required
            class="{{ $inputClass }} min-h-28 resize-y @error('body') {{ $errorClass }} @enderror"
            placeholder="تجربه خود از خرید یا مراجعه به این فروشگاه را بنویسید..."
        >{{ old('body') }}</textarea>
    </div>

    <button type="submit" class="ps-btn-primary mt-5">
        ارسال نظر
    </button>
</form>

@once
    @push('scripts')
        <script>
            (function () {
                const initRatingPickers = function (root) {
                    root.querySelectorAll('[data-rating-picker]').forEach(function (picker) {
                        if (picker.dataset.ratingReady === 'true') {
                            return;
                        }

                        const input = picker.querySelector('[data-rating-input]');
                        const label = picker.querySelector('[data-rating-label]');
                        const stars = Array.from(picker.querySelectorAll('[data-rating-star]'));

                        if (!input || stars.length === 0) {
                            return;
                        }

                        picker.dataset.ratingReady = 'true';

                        const paintStars = function (value) {
                            stars.forEach(function (button) {
                                const starValue = Number(button.dataset.ratingStar);
                                const active = value > 0 && starValue <= value;
                                const path = button.querySelector('path');

                                button.dataset.starActive = active ? 'true' : 'false';
                                button.setAttribute('aria-pressed', active ? 'true' : 'false');

                                if (path) {
                                    path.setAttribute('fill', active ? '#d4a017' : '#e2e8f0');
                                }
                            });

                            if (label) {
                                label.textContent = value > 0
                                    ? 'امتیاز انتخاب‌شده: ' + value + ' از ۵'
                                    : 'یک امتیاز انتخاب کنید';
                                label.classList.toggle('ps-comment-rating-label--selected', value > 0);
                                label.classList.toggle('text-ink-muted', value <= 0);
                            }
                        };

                        stars.forEach(function (button) {
                            button.addEventListener('mouseenter', function () {
                                paintStars(Number(button.dataset.ratingStar));
                            });

                            button.addEventListener('mouseleave', function () {
                                paintStars(Number(input.value || 0));
                            });

                            button.addEventListener('click', function () {
                                const value = Number(button.dataset.ratingStar);
                                input.value = String(value);
                                paintStars(value);
                            });
                        });

                        paintStars(Number(input.value || 0));
                    });
                };

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', function () {
                        initRatingPickers(document);
                    });
                } else {
                    initRatingPickers(document);
                }
            })();
        </script>
    @endpush
@endonce
