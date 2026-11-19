<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string,array<int,mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40', 'regex:/^[0-9+\-\s()]{6,40}$/'],
            'email' => ['nullable', 'email', 'max:190'],
            'city' => ['required', 'string', 'max:100'],
            'address' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'string', 'max:30'],
            'checkout_token' => ['required', 'string', 'uuid'],
            // حقل مصيدة للروبوتات: يجب أن يبقى فارغًا
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.regex' => 'رقم الهاتف غير صحيح.',
        ];
    }
}
