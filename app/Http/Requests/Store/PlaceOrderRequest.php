<?php

namespace App\Http\Requests\Store;

use App\Models\Order;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'billing_first_name' => 'required|string|max:255',
            'billing_phone' => 'required|phone:LB',
            // Not asked when the customer picks the order up at the store
            'billing_address_1' => 'required_unless:delivery_method,pickup|nullable|string|max:1000',
            'billing_city' => 'required_unless:delivery_method,pickup|nullable|string|max:255',
            'billing_email' => 'nullable|email|max:255',
            // Pages cached before pickup existed send nothing: delivery
            'delivery_method' => ['nullable', Rule::in(Order::DELIVERY_METHODS)],
            'newsletter' => 'nullable|boolean',
            'checkout_token' => 'nullable|string|max:64',
        ];
    }

    public function messages(): array
    {
        return [
            'billing_phone.phone' => 'The phone number must be a valid Lebanese phone number.',
            'billing_address_1.required_unless' => 'Please write your address.',
            'billing_city.required_unless' => 'Please write your city or area.',
        ];
    }

    public function isPickup(): bool
    {
        return $this->input('delivery_method') === Order::PICKUP;
    }
}
