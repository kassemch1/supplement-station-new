<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['cart_id', 'product_id', 'quantity','selected_options'];

//    public function product()
//    {
//        return $this->belongsTo(Product::class);
//    }
//
//    public function options()
//    {
//        return $this->belongsToMany(ProductOption::class, 'cart_item_options');
//    }
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
}
