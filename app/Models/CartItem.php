<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['cart_id', 'product_id', 'quantity', 'selected_options'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function options()
    {
        return $this->hasMany(CartItemOption::class);
    }

    // The options the customer picked, e.g. ['Flavor' => 'Vanilla']
    public function selectedOptions(): array
    {
        return json_decode($this->selected_options ?? '', true) ?: [];
    }

    public function hasOptions(array $options): bool
    {
        return $this->selectedOptions() == $options;
    }

    // The picture of the chosen flavour/colour when the admin added one, else the product's first picture.
    // Load product.images and product.productOptions.option first to keep it query-free.
    public function image(): ?string
    {
        $options = $this->selectedOptions();
        $optionImage = $this->product->productOptions
            ->first(fn ($productOption) => $productOption->image && $productOption->option
                && (string) ($options[$productOption->option->option_name] ?? '') === (string) $productOption->option_value)
            ?->image;

        return $optionImage ?? $this->product->images->first()?->url;
    }

    public function lineTotal(): float
    {
        return $this->product->price_after_discount * $this->quantity;
    }
}
