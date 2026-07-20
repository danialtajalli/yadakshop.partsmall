<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Validator;
class StoreShopCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'fullname' => ['required', 'string', 'max:255'],
            'mobile' => ['nullable', 'string', 'max:20', 'regex:/^09\d{9}$/', 'required'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'company_url' => ['prohibited'],
            'cf-turnstile-response' => ['required'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function (Validator $validator) {

            $response = Http::asForm()->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'secret'   => config('services.turnstile.secret'),
                    'response' => $this->input('cf-turnstile-response'),
                    'remoteip' => $this->ip(),
                ]
            );

            if (!($response->json()['success'] ?? false)) {
                $validator->errors()->add(
                    'captcha',
                    'کپچای امنیتی معتبر نیست.'
                );
            }
        });
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'fullname' => 'نام',
            'mobile' => 'شماره موبایل',
            'body' => 'متن نظر',
            'rating' => 'امتیاز',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'fullname.required' => 'لطفاً نام خود را وارد کنید.',
            'fullname.string' => 'نام باید یک رشته باشد.',
            'fullname.max' => 'نام نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'mobile.nullable' => 'شماره موبایل میتواند خالی باشد.',
            'mobile.string' => 'شماره موبایل باید یک رشته باشد.',
            'mobile.max' => 'شماره موبایل نباید بیشتر از ۲۰ کاراکتر باشد.',
            'mobile.regex' => 'شماره موبایل معتبر نیست. نمونه صحیح: 09121234567',
            'mobile.required' => 'لطفاً شماره موبایل خود را وارد کنید.',
            'body.required' => 'لطفاً متن نظر خود را وارد کنید.',
            'body.string' => 'متن نظر باید یک رشته باشد.',
            'body.min' => 'متن نظر باید حداقل ۱۰ کاراکتر باشد.',
            'body.max' => 'متن نظر نباید بیشتر از ۲۰۰۰ کاراکتر باشد.',
            'rating.required' => 'لطفاً امتیاز خود را انتخاب کنید.',
            'cf-turnstile-response.required' => 'لطفاً کپچای امنیتی را تکمیل کنید.',
            
        ];
    }
}
