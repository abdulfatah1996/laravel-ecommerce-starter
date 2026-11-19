<?php

namespace App\Http\Requests;

use App\Support\Colors;
use DateTimeZone;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class InstallRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string,array<int,mixed>> */
    public function rules(): array
    {
        $hex = 'regex:'.Colors::HEX_REGEX;

        return [
            'install_token' => ['nullable', 'string', 'max:200'],

            'store_name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'email' => ['nullable', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:40'],
            'country' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:255'],

            'currency_code' => ['required', Rule::in(array_keys(config('store.currencies')))],
            'timezone' => ['required', Rule::in(DateTimeZone::listIdentifiers())],
            'locale' => ['required', Rule::in(['ar'])],

            'owner_name' => ['required', 'string', 'max:120'],
            'owner_email' => ['required', 'email', 'max:190', 'unique:users,email'],
            'owner_password' => ['required', 'confirmed', Password::min(10)->letters()->numbers()],

            'primary_color' => ['required', $hex],
            'secondary_color' => ['required', $hex],
            'accent_color' => ['required', $hex],
        ];
    }
}
