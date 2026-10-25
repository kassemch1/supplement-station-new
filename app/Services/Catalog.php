<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

// The shop's product lists: filters (category, brand, price, stock, sale), sorting, counts for the
// filter buttons, product data for the quick view, and the "complete your stack" suggestions.
class Catalog
{
    public const SORTS = [
        'default' => 'Recommended',
        'best-selling' => 'Best selling',
        'newest' => 'Newest',
        'low-to-high' => 'Price: low to high',
        'high-to-low' => 'Price: high to low',
        'name' => 'Name: A to Z',
    ];

    // What a customer pays, in SQL (same as Product::price_after_discount)
    public const FINAL_PRICE_SQL = '(price * (1 - discount / 100.0))';

    // For each main category, the main categories whose products go well with it (by slug)
    private const COMPLEMENTS = [
        'protein' => ['creatine', 'accessories', 'snacks-carbs', 'amino-acids'],
        'creatine' => ['protein', 'pre-workout', 'accessories'],
        'mass-gainers' => ['creatine', 'accessories', 'protein'],
        'pre-workout' => ['creatine', 'amino-acids', 'protein'],
        'amino-acids' => ['protein', 'pre-workout', 'creatine'],
        'fat-burner' => ['protein', 'health-wellness', 'amino-acids'],
        'health-wellness' => ['protein', 'testosterone-boosters', 'amino-acids'],
        'testosterone-boosters' => ['health-wellness', 'protein', 'creatine'],
        'snacks-carbs' => ['protein', 'creatine'],
        'accessories' => ['protein', 'creatine', 'pre-workout'],
    ];

    /** The filters a shop request asks for, cleaned up */
    public static function filters(Request $request): array
    {
        $brandSlugs = array_values(array_filter(array_map('trim', explode(',', strtolower(self::text($request, 'brand') ?? '')))));
        $sort = self::text($request, 'orderby') ?? 'default';
        $min = self::text($request, 'min');
        $max = self::text($request, 'max');

        return [
            'search' => self::search($request),
            'brands' => Brand::cachedAll()->whereIn('slug', $brandSlugs)->values(),
            'min' => is_numeric($min) ? max(0, (float) $min) : null,
            'max' => is_numeric($max) ? max(0, (float) $max) : null,
            'in_stock' => filter_var(self::text($request, 'in_stock'), FILTER_VALIDATE_BOOLEAN),
            'sale' => filter_var(self::text($request, 'sale'), FILTER_VALIDATE_BOOLEAN),
            'sort' => isset(self::SORTS[$sort]) ? $sort : 'default',
        ];
    }

    // A parameter as text; anything else (?brand[]=x from a bot or a mangled link) counts as not sent
    public static function text(Request $request, string $key): ?string
    {
        $value = $request->input($key);

        return is_scalar($value) ? (string) $value : null;
    }

    // The menu search, the home search and the shop search each send their own parameter
    private static function search(Request $request): ?string
    {
        foreach (['shop-search', 'home-search', 'q'] as $field) {
            $value = trim(self::text($request, $field) ?? '');
            if ($value !== '') {
                return mb_substr($value, 0, 100);
            }
        }

        return null;
    }

    // Products of a category (with its subcategories) matching the search; brand/price/stock/sale come on top
    public static function scope(?Category $category, ?string $search): Builder
    {
        return Product::query()
            ->when($category, fn ($q) => $q->whereIn('category_id', $category->familyIds()))
            ->when($search !== null, fn ($q) => $q->whereIn('products.id', ProductSearch::ids($search) ?: [0]));
    }

    public static function apply(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['brands']->isNotEmpty(), fn ($q) => $q->whereIn('brand_id', $filters['brands']->pluck('id')))
            // The limits are numbers already (see filters()); written into the SQL as numbers so every
            // database compares them as numbers
            ->when($filters['min'] !== null, fn ($q) => $q->whereRaw(self::FINAL_PRICE_SQL . ' >= ' . number_format($filters['min'], 2, '.', '')))
            ->when($filters['max'] !== null, fn ($q) => $q->whereRaw(self::FINAL_PRICE_SQL . ' <= ' . number_format($filters['max'], 2, '.', '')))
            ->when($filters['in_stock'], fn ($q) => $q->where('stock', 1))
            ->when($filters['sale'], fn ($q) => $q->where('discount', '>', 0));
    }

    // Every sort shows what can be bought first and ends on the id, so the order is the same on every
    // request: "load more" then never repeats or skips a product between pages
    // A search sorted as "Recommended" shows the best matches first
    public static function sort(Builder $query, string $sort, ?string $search = null): Builder
    {
        $query->orderByDesc('products.stock');

        if ($sort === 'default' && $search !== null && ($ids = ProductSearch::ids($search))) {
            $cases = implode(' ', array_map(fn ($id, $rank) => 'WHEN ' . (int) $id . ' THEN ' . $rank, $ids, array_keys($ids)));

            return $query->orderByRaw("CASE products.id {$cases} ELSE " . count($ids) . ' END')->orderByDesc('products.id');
        }

        match ($sort) {
            'low-to-high' => $query->orderByRaw(self::FINAL_PRICE_SQL . ' ASC'),
            'high-to-low' => $query->orderByRaw(self::FINAL_PRICE_SQL . ' DESC'),
            'newest' => $query->orderByDesc('products.created_at'),
            'name' => $query->orderBy('products.name'),
            'best-selling' => $query->orderByDesc(self::unitsSold())->orderByDesc('products.created_at'),
            // Best sellers, then products on sale, then the newest
            default => $query->orderByDesc(self::unitsSold())->orderByDesc(DB::raw('products.discount > 0'))
                ->orderByDesc('products.created_at'),
        };

        return $query->orderByDesc('products.id');
    }

    // Units of the product in delivered orders, as a subquery to sort by
    private static function unitsSold(): \Illuminate\Database\Query\Builder
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereColumn('order_items.product_id', 'products.id')
            ->where('orders.status', 'delivered')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0)');
    }

    // [brand_id => number of products] for the brand buttons of the current list
    public static function brandCounts(Builder $scope): Collection
    {
        return (clone $scope)->whereNotNull('brand_id')->groupBy('brand_id')
            ->pluck(DB::raw('COUNT(*)'), 'brand_id')->map(fn ($n) => (int) $n);
    }

    // [category_id => products] including subcategories, so empty categories can be hidden
    public static function categoryCounts(): Collection
    {
        $own = DB::table('products')->whereNull('deleted_at')->groupBy('category_id')
            ->pluck(DB::raw('COUNT(*)'), 'category_id')->map(fn ($n) => (int) $n);

        return Category::cachedAll()->mapWithKeys(fn (Category $category) => [
            $category->id => collect($category->familyIds())->sum(fn ($id) => $own[$id] ?? 0),
        ]);
    }

    // Lowest and highest price a customer pays in the current list, for the price filter
    public static function priceRange(Builder $scope): array
    {
        $row = (clone $scope)->selectRaw('MIN(' . self::FINAL_PRICE_SQL . ') AS lo, MAX(' . self::FINAL_PRICE_SQL . ') AS hi')->toBase()->first();

        return [(int) floor((float) ($row->lo ?? 0)), (int) ceil((float) ($row->hi ?? 0))];
    }

    // Product ids by units sold in delivered orders, best first (refreshed every hour)
    public static function bestSellerIds(int $limit = 24): array
    {
        $ids = Cache::remember('store.best_sellers', 3600, fn () => OrderItem::select('product_id')
            ->whereNotNull('product_id')
            ->whereHas('order', fn ($query) => $query->where('status', 'delivered'))
            ->groupBy('product_id')
            ->orderByDesc(DB::raw('SUM(quantity)'))
            ->limit(60)
            ->pluck('product_id')
            ->map(fn ($id) => (int) $id)
            ->all());

        return array_slice($ids, 0, $limit);
    }

    // Products that go with what is in the cart: from complementary categories, in stock, not in the
    // cart yet; best sellers and products on sale first. An empty cart gets the best sellers.
    public static function suggestions(array $cartProductIds, int $limit = 10): Collection
    {
        $categories = Category::cachedAll();
        $mainSlugs = Product::whereIn('id', $cartProductIds)->pluck('category_id')->unique()
            ->map(fn ($id) => $categories->firstWhere('id', $id)?->main()->slug)
            ->filter()->unique();

        $wanted = $mainSlugs->flatMap(fn ($slug) => self::COMPLEMENTS[$slug] ?? [])->unique()->diff($mainSlugs);
        $categoryIds = $categories->whereNull('parent_id')->whereIn('slug', $wanted->all())
            ->flatMap(fn (Category $main) => $main->familyIds())->all();

        $best = array_flip(self::bestSellerIds(60));
        $candidates = Product::query()
            ->where('stock', 1)
            ->whereNotIn('id', $cartProductIds ?: [0])
            ->when($categoryIds, fn ($q) => $q->whereIn('category_id', $categoryIds))
            ->when(!$categoryIds, fn ($q) => $q->whereIn('id', array_keys($best) ?: [0]))
            ->with('images', 'brand', 'productOptions')
            ->limit(120)
            ->get();

        // Fewer than a row: fill with best sellers from anywhere
        if ($candidates->count() < $limit) {
            $candidates = $candidates->merge(Product::where('stock', 1)
                ->whereNotIn('id', array_merge($cartProductIds, $candidates->pluck('id')->all()) ?: [0])
                ->with('images', 'brand', 'productOptions')
                ->inRandomOrder()->limit($limit)->get());
        }

        // One or two per category so the row isn't all shakers
        return $candidates
            ->sortBy(fn (Product $p) => [isset($best[$p->id]) ? $best[$p->id] : 999, $p->discount > 0 ? 0 : 1])
            ->groupBy('category_id')
            ->flatMap(fn ($group) => $group->take(2))
            ->sortBy(fn (Product $p) => [isset($best[$p->id]) ? $best[$p->id] : 999, $p->discount > 0 ? 0 : 1])
            ->take($limit)
            ->map(fn (Product $p) => self::card($p))
            ->values();
    }

    // What the cart drawer and the suggestion row need to show a product card
    public static function card(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'url' => $product->url(),
            'image' => ($image = $product->images->first()) ? asset($image->url) : null,
            'brand' => $product->brand?->name,
            'price' => round((float) $product->price, 2),
            'final_price' => round($product->price_after_discount, 2),
            'discount' => (float) $product->discount,
            'in_stock' => (bool) $product->stock,
            'has_options' => $product->relationLoaded('productOptions') ? $product->productOptions->isNotEmpty() : $product->productOptions()->exists(),
        ];
    }

    // Everything the quick view needs: pictures, price and the option buttons (with their pictures)
    public static function quickView(Product $product): array
    {
        $product->loadMissing('images', 'brand', 'productOptions.option');
        $category = Category::cachedAll()->firstWhere('id', $product->category_id);

        return self::card($product) + [
            'images' => $product->images->map(fn ($image) => asset($image->url))->values(),
            'category' => $category?->name,
            'description' => \Illuminate\Support\Str::limit(trim(strip_tags((string) $product->description)), 220),
            'options' => $product->productOptions
                ->filter(fn ($productOption) => $productOption->option)
                ->groupBy(fn ($productOption) => $productOption->option->option_name)
                ->map(fn ($values, $name) => [
                    'name' => $name,
                    'values' => $values->map(fn ($value) => [
                        'value' => $value->option_value,
                        'in_stock' => (bool) $value->stock,
                        'image' => $value->imageUrl(),
                    ])->values(),
                ])->values(),
        ];
    }
}
