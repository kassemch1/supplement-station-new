<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'billing_first_name' => 'required|string|max:255',
            'billing_phone' => 'required|phone:LB',
            'billing_address_1' => 'required|string|max:1000',
            'billing_city' => 'required|string|max:255',
            'billing_email' => 'nullable|email|max:255',
            'newsletter' => 'nullable|boolean',
            'checkout_token' => 'nullable|string|max:64',
        ];
    }

    public function messages(): array
    {
        return ['billing_phone.phone' => 'The phone number must be a valid Lebanese phone number.'];
    }
}
