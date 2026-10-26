<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Services\Catalog;
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

    protected $fillable = ['name', 'description', 'price', 'category_id', 'brand_id', 'stock', 'discount'];

    protected static function booted(): void
    {
        // The menus hide categories without products, from cached numbers (Catalog::categoryCounts)
        static::saved(fn () => Catalog::forgetCategoryCounts());
        static::deleted(fn () => Catalog::forgetCategoryCounts());
        static::restored(fn () => Catalog::forgetCategoryCounts());
    }

    public function productOptions()
    {
        return $this->hasMany(ProductOption::class);
    }

    public function getCategory()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    // In the order the admin dragged them into; the first one is the main picture
    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'id')->orderBy('sort_order')->orderBy('id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // What a product card shows besides the product: its pictures, brand and star rating. The brand is
    // taken from the cached brand list, so it costs no query.
    public function scopeWithCardData($query)
    {
        return $query->with('images')->withCount('reviews')->withAvg('reviews', 'rating')
            ->afterQuery(function ($products) {
                $brands = Brand::cachedAll()->keyBy('id');
                foreach ($products as $product) {
                    $product->setRelation('brand', $product->brand_id ? $brands->get($product->brand_id) : null);
                }
            });
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
