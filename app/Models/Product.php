<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    // Deleting a product archives it: it disappears from the shop but past orders keep pointing at it
    use HasFactory, SoftDeletes;
    // The page URL is /products/{slug}, made from the name (see HasSlug)
    use HasSlug;

    protected $fillable = ['name', 'description', 'price', 'category_id', 'stock', 'discount'];

    public function productOptions()
    {
        return $this->hasMany(ProductOption::class);
    }

    public function getCategory()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // What a product card shows besides the product: its pictures and star rating
    public function scopeWithCardData($query)
    {
        return $query->with('images')->withCount('reviews')->withAvg('reviews', 'rating');
    }

    // Products for cards, in the order of $ids (best sellers first, special offers in the admin's order)
    public static function cards(array $ids): Collection
    {
        $order = array_flip(array_values($ids));

        return static::whereIn('id', $ids)->withCardData()->get()
            ->sortBy(fn (Product $product) => $order[$product->id])
            ->values();
    }

    public function url(): string
    {
        return route('products.show', $this->slug ?: $this->id);
    }

    // The price a customer pays: the one place the product discount (a percentage) is applied
    public function getPriceAfterDiscountAttribute(): float
    {
        return $this->discount ? $this->price * (1 - $this->discount / 100) : (float) $this->price;
    }
}
