<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Links a cart line to the exact product option rows the customer picked
class CartItemOption extends Model
{
    use HasFactory;

    protected $fillable = ['cart_item_id', 'product_option_id'];

    public function cartItem()
    {
        return $this->belongsTo(CartItem::class);
    }

    public function productOption()
    {
        return $this->belongsTo(ProductOption::class);
    }
}
