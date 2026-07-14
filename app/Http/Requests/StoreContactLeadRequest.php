<?php

namespace App\Http\Requests;

use App\Support\IranianMobile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreContactLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('phone')) {
            $this->merge([
                'phone' => IranianMobile::normalize((string) $this->input('phone')),
            ]);
        }
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:15'],
            'message' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $phone = (string) $this->input('phone');

            if ($phone !== '' && ! IranianMobile::isValid($phone)) {
                $validator->errors()->add('phone', 'شماره موبایل معتبر نیست. نمونه صحیح: 09121234567');
            }
        });
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'first_name' => 'نام',
            'last_name' => 'نام خانوادگی',
            'phone' => 'شماره موبایل',
            'message' => 'پیام',
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'first_name.required' => 'نام را وارد کنید.',
            'first_name.max' => 'نام بیش از حد طولانی است.',
            'last_name.required' => 'نام خانوادگی را وارد کنید.',
            'last_name.max' => 'نام خانوادگی بیش از حد طولانی است.',
            'phone.required' => 'شماره موبایل را وارد کنید.',
            'message.required' => 'متن پیام را وارد کنید.',
            'message.min' => 'متن پیام باید حداقل 5 کاراکتر باشد.',
            'message.max' => 'متن پیام بیش از حد طولانی است.',
        ];
    }
}
