@props([
    'shop',
    'idSuffix' => 'default',
])

@php
    $inputClass = 'w-full rounded-xl border border-line bg-white px-3.5 py-2.5 text-sm text-ink outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20';
    $errorClass = 'ps-input--error';
    $nameId = 'comment-fullname-'.$idSuffix;
    $mobileId = 'comment-mobile-'.$idSuffix;
    $bodyId = 'comment-body-'.$idSuffix;
    $honeypotId = 'company-url-'.$idSuffix;
    $arcaptchaSiteKey = config('arcaptcha.site_key');
@endphp

@once
    @push('head')
        @if (filled(config('arcaptcha.site_key')))
            @arcaptchaScript
        @endif
    @endpush
@endonce

<form
    method="post"
    action="{{ route('shop.comments.store', $shop->slug) }}"
    class="ps-card mb-6 p-5 sm:p-6"
    data-shop-comment-form
    x-data="shopCommentForm({
        action: @js(route('api.shop.comments.store', $shop->slug)),
        csrf: @js(csrf_token()),
    })"
    @submit.prevent="submit"
>
    @csrf

    <h3 class="text-base font-bold text-ink">ثبت نظر شما</h3>
    <p class="mt-1 text-sm text-ink-muted">نظر شما پس از بررسی در این صفحه نمایش داده می‌شود.</p>

    <x-ui.form-errors class="mt-5" />

    <div
        class="ps-form-errors mt-5"
        role="alert"
        aria-live="polite"
        x-cloak
        x-show="errors.length > 0"
    >
        <p class="ps-form-errors__title">لطفاً موارد زیر را اصلاح کنید:</p>
        <ul class="ps-form-errors__list">
            <template x-for="(error, index) in errors" :key="index">
                <li class="ps-form-errors__item" x-text="error"></li>
            </template>
        </ul>
    </div>

    <div class="absolute -left-[9999px] h-0 w-0 overflow-hidden" aria-hidden="true">
        <label for="{{ $honeypotId }}">Company URL</label>
        <input
            type="text"
            name="company_url"
            id="{{ $honeypotId }}"
            tabindex="-1"
            autocomplete="off"
            x-model="form.company_url"
        >
    </div>

    <div class="mt-5 grid gap-4 sm:grid-cols-2">
        <div>
            <label for="{{ $nameId }}" class="mb-1.5 block text-xs font-medium text-ink-muted">نام و نام خانوادگی</label>
            <input
                type="text"
                name="fullname"
                id="{{ $nameId }}"
                autocomplete="name"
                x-model="form.fullname"
                :class="hasFieldError('fullname') ? '{{ $errorClass }}' : ''"
                class="{{ $inputClass }}"
            >
        </div>

        <div>
            <label for="{{ $mobileId }}" class="mb-1.5 block text-xs font-medium text-ink-muted">شماره موبایل</label>
            <input
                type="tel"
                name="mobile"
                id="{{ $mobileId }}"
                autocomplete="tel"
                inputmode="numeric"
                pattern="09[0-9]{9}"
                maxlength="11"
                dir="ltr"
                x-model="form.mobile"
                @input="sanitizeMobile"
                :class="hasFieldError('mobile') ? '{{ $errorClass }}' : ''"
                class="{{ $inputClass }} tabular-nums"
            >
        </div>
    </div>

    <div class="mt-4">
        <span class="mb-1.5 block text-xs font-medium text-ink-muted">امتیاز شما</span>
        <div
            class="ps-comment-star-picker flex flex-wrap items-center"
            :class="hasFieldError('rating') ? 'ps-comment-star-picker--error' : ''"
            data-rating-picker
        >
            <input type="hidden" name="rating" x-model="form.rating" data-rating-input>
            <template x-for="star in 5" :key="star">
                <button
                    type="button"
                    :data-rating-star="star"
                    :data-star-active="displayRating >= star ? 'true' : 'false'"
                    :aria-label="'امتیاز ' + toPersianDigits(star) + ' از ۵'"
                    :aria-pressed="form.rating >= star ? 'true' : 'false'"
                    @mouseenter="hoverRating = star"
                    @mouseleave="hoverRating = 0"
                    @click="setRating(star)"
                >
                    <p class="text-xs" x-text="toPersianDigits(star)"></p>
                    <svg viewBox="0 0 20 20" aria-hidden="true" data-star-icon>
                        <path :fill="starFill(star)" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 0 0 .95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 0 0-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 0 0-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 0 0-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 0 0 .951-.69l1.07-3.292Z" />
                    </svg>
                </button>
            </template>
            <span
                class="ms-2 text-xs"
                :class="form.rating > 0 ? 'ps-comment-rating-label--selected' : 'text-ink-muted'"
                data-rating-label
                x-text="ratingLabel"
            ></span>
        </div>
    </div>

    <div class="mt-4">
        <label for="{{ $bodyId }}" class="mb-1.5 block text-xs font-medium text-ink-muted">متن نظر</label>
        <textarea
            name="body"
            id="{{ $bodyId }}"
            rows="4"
            x-model="form.body"
            :class="hasFieldError('body') ? '{{ $errorClass }}' : ''"
            class="{{ $inputClass }} min-h-28 resize-y"
            placeholder="تجربه خود از خرید یا مراجعه به این فروشگاه را بنویسید..."
        ></textarea>
    </div>

    @if (filled($arcaptchaSiteKey))
        <div class="mt-5 flex justify-center">
            @arcaptchaWidget(['lang' => 'fa'])
        </div>
    @endif

    <button type="submit" class="ps-btn-primary mt-5" :disabled="submitting" :aria-busy="submitting">
        <span x-show="!submitting">ارسال نظر</span>
        <span x-cloak x-show="submitting">در حال ارسال...</span>
    </button>
</form>
