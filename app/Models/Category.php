<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    // The category page is /shop/{slug}, made from the name (see HasSlug)
    use HasFactory, HasSlug;

    private const CACHE_KEY = 'store.categories';

    protected static function booted(): void
    {
        // The admin adds, renames and deletes categories through these models, so the menu updates at once
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }

    // Every store page shows the category menu; it is read from the database only after a change
    public static function cachedAll(): Collection
    {
        return Cache::rememberForever(self::CACHE_KEY, fn () => static::all());
    }

    public function url(): string
    {
        return route('shop.category', $this->slug ?: $this->id);
    }

    public function getProducts()
    {
        return $this->hasMany(Product::class,'category_id','id');
    }
}
