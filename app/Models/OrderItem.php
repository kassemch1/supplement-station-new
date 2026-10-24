<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['order_id', 'product_id', 'product_name', 'product_image', 'quantity', 'price', 'selected_options'];

    // Includes archived products, so past orders still show what was bought
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    // Name and image as they were when ordered, falling back to the product for older lines
    public function getDisplayNameAttribute(): string
    {
        return $this->product_name ?? $this->product?->name ?? 'Deleted product';
    }

    public function getDisplayImageAttribute(): ?string
    {
        return $this->product_image ?? $this->product?->images->first()?->url;
    }

    public function options()
    {
        return $this->belongsToMany(ProductOption::class, 'order_item_options');
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
