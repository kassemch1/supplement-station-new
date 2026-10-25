<?php

namespace App\Http\Requests\Admin;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

// The add and edit product forms
class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        // A new product needs a picture; an edited one only if it has none left
        $product = $this->input('product_id') ? Product::find($this->input('product_id')) : null;
        $needsImages = !$product || !$product->images()->exists();

        return [
            'product_id' => 'sometimes|integer|exists:products,id',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01',
            'category_id' => 'required|integer|exists:categories,id',
            'discount' => 'required|numeric|between:0,100',
            'description' => 'required|string',
            'stock' => 'required|in:in-stock,out-of-stock',
            'images' => ($needsImages ? 'required|' : 'nullable|') . 'array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'The product name is required.',
            'price.required' => 'The price is required.',
            'price.min' => 'The price must be greater than zero.',
            'category_id.required' => 'The category is required.',
            'category_id.exists' => 'Please choose one of the categories.',
            'discount.required' => 'The discount is required.',
            'discount.between' => 'The discount must be between 0 and 100.',
            'description.required' => 'The description is required.',
            'stock.required' => 'The stock status is required.',
            'stock.in' => 'The stock status must be either "in-stock" or "out-of-stock".',
            'images.required' => 'At least one image is required.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Each image must be a file of type: jpeg, png, jpg, gif, webp.',
            'images.*.max' => 'Each image must not exceed 2MB.',
        ];
    }

    // The product columns, as saved
    public function productData(): array
    {
        return [
            'name' => $this->input('name'),
            'price' => $this->input('price'),
            'category_id' => $this->input('category_id'),
            'discount' => $this->input('discount'),
            'description' => $this->input('description'),
            'stock' => $this->input('stock') === 'in-stock',
        ];
    }
}
