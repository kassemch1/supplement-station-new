<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

// The add and edit plan forms (the price cards on the home page)
class PlanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => 'required|string|max:255',
            'price' => 'required|numeric',
            'point1' => 'nullable|string|max:255',
            'point2' => 'nullable|string|max:255',
            'point3' => 'nullable|string|max:255',
            'point4' => 'nullable|string|max:255',
            'point5' => 'nullable|string|max:255',
        ];
    }
}
