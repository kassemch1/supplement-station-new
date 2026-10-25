<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

// The create and edit coupon forms. The code is set once, when the coupon is created.
class CouponRequest extends FormRequest
{
    private function creating(): bool
    {
        return $this->isMethod('post');
    }

    protected function prepareForValidation(): void
    {
        // Codes are stored in capitals so "summer10" and "SUMMER10" are the same coupon
        if ($this->creating() && $this->filled('code')) {
            $this->merge(['code' => strtoupper(trim($this->input('code')))]);
        }
    }

    public function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:reusable,single_use',
            'discount_percentage' => 'required|numeric|between:0.01,100',
            'minimum_order_amount' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            // the column is a MySQL TIMESTAMP, which ends in January 2038
            'expires_at' => 'nullable|date|before:2038-01-01' . ($this->creating() ? '|after:now' : ''),
        ];

        if ($this->creating()) {
            $rules = ['code' => 'required|string|max:255|unique:coupons,code'] + $rules;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'code.required' => 'The coupon code is required.',
            'code.unique' => 'This coupon code is already in use.',
            'name.required' => 'The coupon name is required.',
            'type.required' => 'The coupon type is required.',
            'type.in' => 'The coupon type must be either reusable or single use.',
            'discount_percentage.required' => 'The discount percentage is required.',
            'discount_percentage.between' => 'The discount percentage must be between 0.01 and 100.',
            'discount_percentage.numeric' => 'The discount percentage must be a valid number.',
            'minimum_order_amount.required' => 'The minimum order amount is required.',
            'minimum_order_amount.min' => 'The minimum order amount must be at least 0.',
            'minimum_order_amount.numeric' => 'The minimum order amount must be a valid number.',
            'max_uses.integer' => 'The maximum uses must be a number.',
            'max_uses.min' => 'The maximum uses must be at least 1.',
            'expires_at.date' => 'The expiry date must be a valid date.',
            'expires_at.after' => 'The expiry date must be in the future.',
            'expires_at.before' => 'The expiry date must be before 2038.',
        ];
    }

    // The coupon columns, as saved: a single-use coupon can be used once
    public function couponData(): array
    {
        $data = $this->validated();
        $data['expires_at'] ??= null;
        $data['max_uses'] = $data['type'] === 'single_use' ? 1 : ($data['max_uses'] ?? null);

        return $data;
    }
}
