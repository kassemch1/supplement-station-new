<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

// The maker of a product (BioTechUSA, Optimum Nutrition...). The shop filters by brand: /shop?brand={slug}
class Brand extends Model
{
    use HasSlug;

    private const CACHE_KEY = 'store.brands';

    protected $fillable = ['name', 'logo', 'sort_order'];

    protected static function booted(): void
    {
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    // Every shop page lists the brands; read from the database only after a change.
    // Kept in memory for the rest of the request (pages ask for it many times), and each caller gets its
    // own copies, so changing one never leaks into another caller
    public static function cachedAll(): Collection
    {
        return Cache::memo()->rememberForever(self::CACHE_KEY, fn () => static::orderBy('sort_order')->orderBy('name')->get())
            ->map(fn (self $model) => clone $model);
    }

    public static function forgetCache(): void
    {
        Cache::memo()->forget(self::CACHE_KEY);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? asset($this->logo) : null;
    }

    // Two letters shown in place of a missing logo, e.g. "LW" for Larry Wheels
    public function initials(): string
    {
        $words = preg_split('/[\s\-]+/', trim($this->name)) ?: [];

        return strtoupper(count($words) > 1 ? mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1) : mb_substr($this->name, 0, 2));
    }
}
