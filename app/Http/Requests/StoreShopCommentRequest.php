<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'mobile' => ['nullable', 'string', 'max:20', 'regex:/^09\d{9}$/'],
            'body' => ['required', 'string', 'min:10', 'max:2000'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'company_url' => ['prohibited'],
        ];
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
            'body.min' => 'متن نظر باید حداقل ۱۰ کاراکتر باشد.',
            'rating.required' => 'لطفاً امتیاز خود را انتخاب کنید.',
            'mobile.max' => 'شماره موبایل نباید بیشتر از ۲۰ کاراکتر باشد.',
            'mobile.regex' => 'شماره موبایل معتبر نیست. نمونه صحیح: 09121234567',
        ];
    }
}
