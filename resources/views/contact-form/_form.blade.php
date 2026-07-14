@php
    $values = [
        'first_name' => old('first_name', ''),
        'last_name' => old('last_name', ''),
        'phone' => old('phone', ''),
        'message' => old('message', ''),
    ];
    $statusMessage = session('contact_status_message');
    $statusType = session('contact_status_type');
@endphp

<style>
    .didar-contact-form {
        font-family: Tahoma, Arial, sans-serif;
        color: #0f172a;
        direction: rtl;
    }

    .didar-contact-form * {
        box-sizing: border-box;
    }

    .didar-contact-form__alert {
        margin-top: 1rem;
        border-radius: 0.75rem;
        padding: 0.875rem 1rem;
        font-size: 0.875rem;
        line-height: 1.6;
    }

    .didar-contact-form__alert--success {
        background: #ecfdf5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }

    .didar-contact-form__alert--error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }

    .didar-contact-form__grid {
        display: grid;
        gap: 1rem;
    }

    @media (min-width: 640px) {
        .didar-contact-form__grid--two {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    .didar-contact-form__field label {
        display: block;
        margin-bottom: 0.375rem;
        font-size: 0.875rem;
        font-weight: 600;
    }

    .didar-contact-form__field input,
    .didar-contact-form__field textarea {
        width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        padding: 0.75rem 0.875rem;
        font: inherit;
        background: #fff;
    }

    .didar-contact-form__field textarea {
        min-height: 8rem;
        resize: vertical;
    }

    .didar-contact-form__field input:focus,
    .didar-contact-form__field textarea:focus {
        outline: 2px solid rgb(63 72 87 / 0.25);
        border-color: #3f4857;
    }

    .didar-contact-form__field input.didar-contact-form__input--error,
    .didar-contact-form__field textarea.didar-contact-form__input--error {
        border-color: #f87171;
        background: #fffafa;
    }

    .didar-contact-form__field input.didar-contact-form__input--error:focus,
    .didar-contact-form__field textarea.didar-contact-form__input--error:focus {
        outline-color: rgb(248 113 113 / 0.25);
        border-color: #ef4444;
    }

    .didar-contact-form__error {
        margin: 0.375rem 0 0;
        font-size: 0.75rem;
        line-height: 1.5;
        color: #dc2626;
    }

    .didar-contact-form__error[hidden] {
        display: none;
    }

    .didar-contact-form__submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        border: 0;
        border-radius: 0.75rem;
        padding: 0.875rem 1rem;
        background: #3f4857;
        color: #fff;
        font: inherit;
        font-weight: 600;
        cursor: pointer;
    }

    .didar-contact-form__submit:hover {
        background: #222c3d;
    }
</style>

<div class="didar-contact-form">
    <form method="post" action="{{ $formAction }}" novalidate data-didar-contact-form>
        @csrf

        <div class="didar-contact-form__grid didar-contact-form__grid--two">
            <div class="didar-contact-form__field">
                <label for="first_name">نام</label>
                <input
                    id="first_name"
                    name="first_name"
                    type="text"
                    value="{{ $values['first_name'] }}"
                    maxlength="100"
                    autocomplete="given-name"
                    aria-describedby="first_name-error"
                    @class(['didar-contact-form__input--error' => $errors->has('first_name')])
                >
                <p class="didar-contact-form__error" id="first_name-error" role="alert" data-field-error="first_name" @if (! $errors->has('first_name')) hidden @endif>
                    {{ $errors->first('first_name') }}
                </p>
            </div>

            <div class="didar-contact-form__field">
                <label for="last_name">نام خانوادگی</label>
                <input
                    id="last_name"
                    name="last_name"
                    type="text"
                    value="{{ $values['last_name'] }}"
                    maxlength="100"
                    autocomplete="family-name"
                    aria-describedby="last_name-error"
                    @class(['didar-contact-form__input--error' => $errors->has('last_name')])
                >
                <p class="didar-contact-form__error" id="last_name-error" role="alert" data-field-error="last_name" @if (! $errors->has('last_name')) hidden @endif>
                    {{ $errors->first('last_name') }}
                </p>
            </div>
        </div>

        <div class="didar-contact-form__grid" style="margin-top: 1rem;">
            <div class="didar-contact-form__field">
                <label for="phone">شماره موبایل</label>
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
                    style="text-align: right;"
                    aria-describedby="phone-error"
                    @class(['didar-contact-form__input--error' => $errors->has('phone')])
                >
                <p class="didar-contact-form__error" id="phone-error" role="alert" data-field-error="phone" @if (! $errors->has('phone')) hidden @endif>
                    {{ $errors->first('phone') }}
                </p>
            </div>

            <div class="didar-contact-form__field">
                <label for="message">پیام</label>
                <textarea
                    id="message"
                    name="message"
                    maxlength="2000"
                    placeholder="پیام خود را بنویسید..."
                    aria-describedby="message-error"
                    @class(['didar-contact-form__input--error' => $errors->has('message')])
                >{{ $values['message'] }}</textarea>
                <p class="didar-contact-form__error" id="message-error" role="alert" data-field-error="message" @if (! $errors->has('message')) hidden @endif>
                    {{ $errors->first('message') }}
                </p>
            </div>
        </div>

        <div style="margin-top: 1rem;">
            <button type="submit" class="didar-contact-form__submit">ارسال پیام</button>
        </div>
    </form>

    @if ($statusMessage)
        <div class="didar-contact-form__alert didar-contact-form__alert--{{ $statusType ?? 'error' }}">
            {{ $statusMessage }}
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
        }

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
