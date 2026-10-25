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

    public function lineTotal(): float
    {
        return $this->product->price_after_discount * $this->quantity;
    }
}
