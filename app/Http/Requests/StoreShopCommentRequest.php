<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShopCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('mobile')) {
            return;
        }

        $this->merge([
            'mobile' => preg_replace('/\D/', '', $this->toEnglishDigits((string) $this->input('mobile'))) ?? '',
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $rules = [
            'fullname' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:11', 'regex:/^09\d{9}$/', 'required'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'company_url' => ['prohibited'],
        ];

        if ($this->shouldVerifyArCaptcha()) {
            $rules['arcaptcha-token'] = ['required', 'arcaptcha'];
        }

        return $rules;
    }

    private function shouldVerifyArCaptcha(): bool
    {
        return filled(config('arcaptcha.secret_key'))
            && ! app()->environment('testing');
    }

    private function toEnglishDigits(string $value): string
    {
        return strtr($value, [
            '۰' => '0',
            '۱' => '1',
            '۲' => '2',
            '۳' => '3',
            '۴' => '4',
            '۵' => '5',
            '۶' => '6',
            '۷' => '7',
            '۸' => '8',
            '۹' => '9',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9',
        ]);
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'fullname' => 'نام',
            'mobile' => 'شماره موبایل',
            'body' => 'متن نظر',
            'rating' => 'امتیاز',
            'arcaptcha-token' => 'کپچای امنیتی',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'fullname.required' => 'لطفاً نام خود را وارد کنید.',
            'fullname.string' => 'نام باید یک رشته باشد.',
            'fullname.max' => 'نام نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'mobile.nullable' => 'شماره موبایل می‌تواند خالی باشد.',
            'mobile.string' => 'شماره موبایل باید یک رشته باشد.',
            'mobile.max' => 'شماره موبایل نباید بیشتر از ۱۱ رقم باشد.',
            'mobile.regex' => 'شماره موبایل معتبر نیست. نمونه صحیح: 09121234567',
            'mobile.required' => 'لطفاً شماره موبایل خود را وارد کنید.',
            'body.required' => 'لطفاً متن نظر خود را وارد کنید.',
            'body.string' => 'متن نظر باید یک رشته باشد.',
            'body.min' => 'متن نظر باید حداقل ۱۰ کاراکتر باشد.',
            'body.max' => 'متن نظر نباید بیشتر از ۲۰۰۰ کاراکتر باشد.',
            'rating.required' => 'لطفاً امتیاز خود را انتخاب کنید.',
            'arcaptcha-token.required' => 'لطفاً کپچای امنیتی را تکمیل کنید.',
            'arcaptcha-token.arcaptcha' => 'کپچای امنیتی معتبر نیست.',
        ];
    }
}
