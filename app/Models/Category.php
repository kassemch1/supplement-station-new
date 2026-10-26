<?php

namespace App\Models;

use App\Models\Concerns\HasSlug;
use App\Services\Catalog;
use App\Support\Icons;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

// A main category (Protein) or a subcategory inside one (Whey Protein, parent_id = Protein).
// Two levels only. A product belongs to one of them: a main category's page shows its own products
// and those of its subcategories.
class Category extends Model
{
    // The category page is /shop/{slug}, made from the name (see HasSlug)
    use HasFactory, HasSlug;

    private const CACHE_KEY = 'store.categories';

    protected $fillable = ['name', 'parent_id', 'icon', 'image', 'sort_order'];

    protected static function booted(): void
    {
        // The admin adds, renames and deletes categories through these models, so the menu updates at once
        static::saved(fn () => static::forgetCache());
        static::deleted(fn () => static::forgetCache());
    }

    // Every store page shows the category menu; it is read from the database only after a change.
    // Kept in memory for the rest of the request (pages ask for it many times), and each caller gets its
    // own copies, so setting relations on them (tree(), the shop menu) never leaks into another caller
    public static function cachedAll(): Collection
    {
        return Cache::memo()->rememberForever(self::CACHE_KEY, fn () => static::orderBy('sort_order')->orderBy('name')->get())
            ->map(fn (self $model) => clone $model);
    }

    public static function forgetCache(): void
    {
        Cache::memo()->forget(self::CACHE_KEY);
    }

    // Main categories, each with its subcategories in ->children (no queries)
    public static function tree(): Collection
    {
        $all = static::cachedAll();
        $children = $all->whereNotNull('parent_id')->groupBy('parent_id');

        return $all->whereNull('parent_id')->values()->each(
            fn (Category $category) => $category->setRelation('children', ($children[$category->id] ?? new Collection())->values())
        );
    }

    // The tree without categories that have no products, so the store never links to an empty page (a main
    // category counts the products of its subcategories). $counts: Catalog::categoryCounts(), when already read
    public static function treeWithProducts(?\Illuminate\Support\Collection $counts = null): Collection
    {
        $counts ??= Catalog::categoryCounts();

        return static::tree()
            ->filter(fn (Category $main) => ($counts[$main->id] ?? 0) > 0)
            ->each(fn (Category $main) => $main->setRelation('children', $main->children->filter(fn (Category $sub) => ($counts[$sub->id] ?? 0) > 0)->values()))
            ->values();
    }

    // This category and its subcategories: the products a category page lists
    public function familyIds(): array
    {
        return static::cachedAll()->where('parent_id', $this->id)->pluck('id')->push($this->id)->all();
    }

    // The main category this one is in (itself when it is a main category)
    public function main(): Category
    {
        return $this->parent_id ? (static::cachedAll()->firstWhere('id', $this->parent_id) ?? $this) : $this;
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function url(): string
    {
        return route('shop.category', $this->slug ?: $this->id);
    }

    public function iconSvg(string $class = 'ss-icon'): string
    {
        return Icons::svg($this->icon, $class);
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset($this->image) : null;
    }

    public function getProducts()
    {
        return $this->hasMany(Product::class,'category_id','id');
    }
}
