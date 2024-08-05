<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItemOption extends Model
{
    use HasFactory;

    protected $fillable = ['cart_item_id', 'product_option_id', 'quantity'];

//    public function product()
//    {
//        return $this->belongsTo(Product::class);
//    }
//
//    public function options()
//    {
//        return $this->belongsToMany(ProductOption::class, 'cart_item_options');
//    }
    public function cartItem()
    {
        return $this->belongsTo(CartItem::class);
    }

    public function productOption()
    {
        return $this->belongsTo(ProductOption::class);
    }
}
