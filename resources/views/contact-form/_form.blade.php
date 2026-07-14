@php
    $values = [
        'first_name' => old('first_name', ''),
        'last_name' => old('last_name', ''),
        'phone' => old('phone', ''),
        'message' => old('message', ''),
    ];
    $statusMessage = session('contact_status_message');
    $statusType = session('contact_status_type');
    $inputClass = 'w-full rounded-xl border border-line bg-white px-3.5 py-2.5 text-sm text-ink shadow-sm outline-none transition placeholder:text-ink-muted focus:border-brand/40 focus:ring-2 focus:ring-brand/20';
    $labelClass = 'mb-1.5 block text-sm font-medium text-ink';
@endphp

<div class="didar-contact-form font-sans text-ink">
    <form method="post" action="{{ $formAction }}" novalidate data-didar-contact-form class="space-y-4">
        @csrf

        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="first_name" class="{{ $labelClass }}">نام</label>
                <input
                    id="first_name"
                    name="first_name"
                    type="text"
                    value="{{ $values['first_name'] }}"
                    maxlength="100"
                    autocomplete="given-name"
                    placeholder="نام خود را وارد کنید"
                    aria-describedby="first_name-error"
                    class="{{ $inputClass }} {{ $errors->has('first_name') ? 'didar-contact-form__input--error' : '' }}"
                >
                <p class="mt-1.5 text-xs leading-5 text-red-600" id="first_name-error" role="alert" data-field-error="first_name" @if (! $errors->has('first_name')) hidden @endif>
                    {{ $errors->first('first_name') }}
                </p>
            </div>

            <div>
                <label for="last_name" class="{{ $labelClass }}">نام خانوادگی</label>
                <input
                    id="last_name"
                    name="last_name"
                    type="text"
                    value="{{ $values['last_name'] }}"
                    maxlength="100"
                    autocomplete="family-name"
                    placeholder="نام خانوادگی خود را وارد کنید"
                    aria-describedby="last_name-error"
                    class="{{ $inputClass }} {{ $errors->has('last_name') ? 'didar-contact-form__input--error' : '' }}"
                >
                <p class="mt-1.5 text-xs leading-5 text-red-600" id="last_name-error" role="alert" data-field-error="last_name" @if (! $errors->has('last_name')) hidden @endif>
                    {{ $errors->first('last_name') }}
                </p>
            </div>
        </div>

        <div>
            <label for="phone" class="{{ $labelClass }}">شماره موبایل</label>
            <input
                id="phone"
                name="phone"
                type="tel"
                value="{{ $values['phone'] }}"
                maxlength="15"
                inputmode="tel"
                autocomplete="tel"
                dir="ltr"
                placeholder="09121234567"
                aria-describedby="phone-error phone-hint"
                class="{{ $inputClass }} text-left tabular-nums tracking-wide {{ $errors->has('phone') ? 'didar-contact-form__input--error' : '' }}"
            >
            <p id="phone-hint" class="mt-1.5 text-xs text-ink-muted">نمونه صحیح: 09121234567</p>
            <p class="mt-1.5 text-xs leading-5 text-red-600" id="phone-error" role="alert" data-field-error="phone" @if (! $errors->has('phone')) hidden @endif>
                {{ $errors->first('phone') }}
            </p>
        </div>

        <div>
            <label for="message" class="{{ $labelClass }}">پیام</label>
            <textarea
                id="message"
                name="message"
                maxlength="2000"
                rows="5"
                placeholder="پیام خود را بنویسید..."
                aria-describedby="message-error"
                class="{{ $inputClass }} min-h-[8rem] resize-y leading-6 {{ $errors->has('message') ? 'didar-contact-form__input--error' : '' }}"
            >{{ $values['message'] }}</textarea>
            <p class="mt-1.5 text-xs leading-5 text-red-600" id="message-error" role="alert" data-field-error="message" @if (! $errors->has('message')) hidden @endif>
                {{ $errors->first('message') }}
            </p>
        </div>

        <button
            type="submit"
            data-contact-submit
            class="ps-contact-submit inline-flex w-full items-center justify-center gap-2 rounded-xl bg-brand px-4 py-3 text-sm font-semibold text-white shadow-sm hover:bg-brand-dark"
        >
            <span data-submit-idle class="inline-flex items-center justify-center gap-2">
                <i class="fa-solid fa-paper-plane text-xs opacity-90" aria-hidden="true"></i>
                <span>ارسال پیام</span>
            </span>
            <span data-submit-loading class="hidden inline-flex items-center justify-center gap-2" aria-hidden="true">
                <i class="fa-solid fa-spinner ps-contact-submit__spinner text-xs" aria-hidden="true"></i>
                <span>در حال ارسال...</span>
            </span>
        </button>
    </form>

    @if ($statusMessage)
        <div
            class="mt-4 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm leading-6
                {{ ($statusType ?? 'error') === 'success'
                    ? 'border-emerald-200 bg-emerald-50 text-emerald-800'
                    : 'border-red-200 bg-red-50 text-red-800' }}"
            role="status"
        >
            <i
                class="mt-0.5 {{ ($statusType ?? 'error') === 'success' ? 'fa-solid fa-circle-check text-emerald-600' : 'fa-solid fa-circle-exclamation text-red-500' }}"
                aria-hidden="true"
            ></i>
            <span>{{ $statusMessage }}</span>
        </div>
    @endif
</div>

<script>
(function () {
    const form = document.querySelector('[data-didar-contact-form]');
    if (!form) {
        return;
    }

    function toEnglishDigits(value) {
        const persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        const arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        const english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return value
            .replace(/[۰-۹]/g, (digit) => english[persian.indexOf(digit)])
            .replace(/[٠-٩]/g, (digit) => english[arabic.indexOf(digit)]);
    }

    function normalizeMobile(phone) {
        let value = toEnglishDigits(phone.trim()).replace(/[^\d+]/g, '');

        if (value.startsWith('+98')) {
            value = '0' + value.slice(3);
        } else if (value.startsWith('0098')) {
            value = '0' + value.slice(4);
        } else if (value.startsWith('98') && value.length === 12) {
            value = '0' + value.slice(2);
        }

        return value;
    }

    function isValidMobile(phone) {
        return /^09\d{9}$/.test(normalizeMobile(phone));
    }

    function setSubmitting(isSubmitting) {
        const button = form.querySelector('[data-contact-submit]');
        const idle = form.querySelector('[data-submit-idle]');
        const loading = form.querySelector('[data-submit-loading]');

        if (!button || !idle || !loading) {
            return;
        }

        button.disabled = isSubmitting;
        button.setAttribute('aria-busy', isSubmitting ? 'true' : 'false');
        idle.classList.toggle('hidden', isSubmitting);
        loading.classList.toggle('hidden', !isSubmitting);
        loading.setAttribute('aria-hidden', isSubmitting ? 'false' : 'true');

        if (isSubmitting && typeof window.startContactNavigationProgress === 'function') {
            window.startContactNavigationProgress();
        }
    }

    function setFieldError(name, message) {
        const input = form.querySelector('[name="' + name + '"]');
        const error = form.querySelector('[data-field-error="' + name + '"]');

        if (!input || !error) {
            return;
        }

        if (message) {
            input.classList.add('didar-contact-form__input--error');
            input.setAttribute('aria-invalid', 'true');
            error.textContent = message;
            error.hidden = false;
        } else {
            input.classList.remove('didar-contact-form__input--error');
            input.removeAttribute('aria-invalid');
            error.textContent = '';
            error.hidden = true;
        }
    }

    function validateField(name, value) {
        const trimmed = value.trim();

        if (name === 'first_name') {
            if (trimmed === '') {
                return 'نام را وارد کنید.';
            }

            if (trimmed.length > 100) {
                return 'نام بیش از حد طولانی است.';
            }

            return '';
        }

        if (name === 'last_name') {
            if (trimmed === '') {
                return 'نام خانوادگی را وارد کنید.';
            }

            if (trimmed.length > 100) {
                return 'نام خانوادگی بیش از حد طولانی است.';
            }

            return '';
        }

        if (name === 'phone') {
            if (trimmed === '') {
                return 'شماره موبایل را وارد کنید.';
            }

            if (!isValidMobile(trimmed)) {
                return 'شماره موبایل معتبر نیست. نمونه صحیح: 09121234567';
            }

            return '';
        }

        if (name === 'message' && trimmed.length > 2000) {
            return 'متن پیام بیش از حد طولانی است.';
        }

        if (name === 'message' && trimmed === '') {
            return 'متن پیام را وارد کنید.';
        }

        if (name === 'message' && trimmed.length < 5) {
            return 'متن پیام باید حداقل 5 کاراکتر باشد.';
        }

        return '';
    }

    function notifyParentHeight() {
        if (window.parent === window) {
            return;
        }

        const height = Math.max(
            document.body.scrollHeight,
            document.documentElement.scrollHeight
        );

        window.parent.postMessage({
            type: 'didar-contact-form-resize',
            height: height,
        }, window.location.origin);
    }

    form.addEventListener('submit', function (event) {
        let valid = true;

        ['first_name', 'last_name', 'phone', 'message'].forEach(function (name) {
            const input = form.querySelector('[name="' + name + '"]');
            const message = validateField(name, input ? input.value : '');
            setFieldError(name, message);

            if (message) {
                valid = false;
            }
        });

        if (!valid) {
            event.preventDefault();
            notifyParentHeight();

            return;
        }

        setSubmitting(true);
        notifyParentHeight();
    });

    form.querySelectorAll('input, textarea').forEach(function (input) {
        input.addEventListener('input', function () {
            setFieldError(input.name, '');
            notifyParentHeight();
        });
    });

    if (window.parent !== window) {
        notifyParentHeight();
        window.addEventListener('load', notifyParentHeight);

        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(notifyParentHeight).observe(document.body);
        }
    }
})();
</script>
