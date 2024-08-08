<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
 

    protected $fillable = ['name', 'description', 'price', 'category_id', 'stock', 'discount'];

    public function productOptions()
    {
        return $this->hasMany(ProductOption::class);
    }

    public function getCategory()
    {
        return $this->belongsTo(Category::class,'category_id','id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class,'product_id','id');
    }
    // Method to get the first image
    public function getFirstImage()
    {
        return $this->getImages()->first();
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
    
    public function getPriceAfterDiscountAttribute()
    {
        return $this->price - ($this->price * $this->discount / 100);
    }
    
}
