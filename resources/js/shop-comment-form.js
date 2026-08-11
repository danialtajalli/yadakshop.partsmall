function toPersianDigits(value) {
    return String(value).replace(/\d/g, (digit) => '۰۱۲۳۴۵۶۷۸۹'[Number(digit)]);
}

function toEnglishDigits(value) {
    return String(value)
        .replace(/[۰-۹]/g, (digit) => String('۰۱۲۳۴۵۶۷۸۹'.indexOf(digit)))
        .replace(/[٠-٩]/g, (digit) => String('٠١٢٣٤٥٦٧٨٩'.indexOf(digit)));
}

function digitsOnly(value) {
    return toEnglishDigits(value).replace(/\D/g, '');
}

export default function shopCommentForm({ action, csrf }) {
    return {
        action,
        csrf,
        submitting: false,
        errors: [],
        fieldErrors: {},
        form: {
            fullname: '',
            mobile: '',
            body: '',
            rating: 0,
            company_url: '',
        },
        hoverRating: 0,

        get displayRating() {
            return this.hoverRating || this.form.rating || 0;
        },

        get ratingLabel() {
            return this.form.rating > 0
                ? `امتیاز انتخاب‌شده: ${toPersianDigits(this.form.rating)} از ۵`
                : 'یک امتیاز انتخاب کنید';
        },

        setRating(value) {
            this.form.rating = value;
            delete this.fieldErrors.rating;
        },

        sanitizeMobile() {
            this.form.mobile = digitsOnly(this.form.mobile);
            delete this.fieldErrors.mobile;
        },

        starFill(star) {
            return this.displayRating >= star ? '#d4a017' : '#e2e8f0';
        },

        toPersianDigits(value) {
            return toPersianDigits(value);
        },

        hasFieldError(field) {
            return Boolean(this.fieldErrors[field]);
        },

        turnstileWidget() {
            return this.$el?.querySelector('.cf-turnstile') ?? null;
        },

        turnstileToken() {
            return this.$el?.querySelector('[name="cf-turnstile-response"]')?.value ?? '';
        },

        resetTurnstile() {
            const widget = this.turnstileWidget();

            if (window.turnstile && widget) {
                window.turnstile.reset(widget);
            }
        },

        resetForm() {
            this.form = {
                fullname: '',
                mobile: '',
                body: '',
                rating: 0,
                company_url: '',
            };
            this.hoverRating = 0;
            this.errors = [];
            this.fieldErrors = {};
            this.resetTurnstile();
        },

        async submit() {
            if (this.submitting) {
                return;
            }

            this.submitting = true;
            this.errors = [];
            this.fieldErrors = {};

            try {
                const body = new FormData();
                body.set('_token', this.csrf);
                body.set('fullname', this.form.fullname);
                body.set('mobile', digitsOnly(this.form.mobile));
                body.set('body', this.form.body);
                body.set('rating', this.form.rating ? String(this.form.rating) : '');

                if (this.form.company_url) {
                    body.set('company_url', this.form.company_url);
                }

                const turnstileToken = this.turnstileToken();

                if (this.turnstileWidget() && ! turnstileToken) {
                    this.errors = ['لطفاً کپچای امنیتی را تکمیل کنید.'];
                    this.submitting = false;

                    return;
                }

                if (turnstileToken) {
                    body.set('cf-turnstile-response', turnstileToken);
                }

                const response = await fetch(this.action, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body,
                });

                const payload = await response.json().catch(() => ({}));

                if (response.status === 422) {
                    this.fieldErrors = payload.errors ?? {};
                    this.errors = Object.values(this.fieldErrors).flat();
                    this.resetTurnstile();

                    return;
                }

                if (! response.ok) {
                    this.errors = [payload.message ?? 'ارسال نظر با خطا مواجه شد. لطفاً دوباره تلاش کنید.'];
                    this.resetTurnstile();

                    return;
                }

                this.resetForm();
                window.dispatchEvent(new CustomEvent('shop-comment-submitted', {
                    detail: {
                        message: payload.message ?? null,
                    },
                }));
            } catch {
                this.errors = ['ارتباط با سرور برقرار نشد. لطفاً دوباره تلاش کنید.'];
                this.resetTurnstile();
            } finally {
                this.submitting = false;
            }
        },
    };
}
