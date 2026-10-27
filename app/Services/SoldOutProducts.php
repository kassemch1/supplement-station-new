<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

// Products customers cannot buy right now (stock 0, not archived), for the dashboard's sold-out card and the
// Sold Out page. Each gets sold_recently: units ordered in the last RECENT_DAYS days (cancelled orders not
// counted), worked out by the database, so the list can be sorted and paged there.
class SoldOutProducts
{
    public const RECENT_DAYS = 90;

    public const SORTS = [
        'best' => 'Best sellers first',
        'longest' => 'Sold out longest',
        'recent' => 'Sold out most recently',
        'name' => 'Name A-Z',
    ];

    // When it sold out; products that sold out before the date was kept use their last change
    private const SINCE = 'COALESCE(products.sold_out_at, products.updated_at)';

    /**
     * @param array{search?: string, category?: ?int, brand?: ?int} $filters
     */
    public static function query(array $filters = [], string $sort = 'best'): Builder
    {
        $sold = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereColumn('order_items.product_id', 'products.id')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.created_at', '>=', now()->subDays(self::RECENT_DAYS))
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0)');

        $search = trim((string) ($filters['search'] ?? ''));
        $category = $filters['category'] ?? null;
        $brand = $filters['brand'] ?? null;

        return Product::where('products.stock', 0)
            ->select('products.*')
            ->selectSub($sold, 'sold_recently')
            ->withCasts(['sold_recently' => 'integer'])
            ->with(['images', 'getCategory', 'brand'])
            ->when($search !== '', fn ($q) => $q->where('products.name', 'like', '%' . addcslashes($search, '%_\\') . '%'))
            // a main category includes its subcategories
            ->when($category, fn ($q) => $q->whereIn('products.category_id', self::categoryIds($category)))
            ->when($brand, fn ($q) => $q->where('products.brand_id', $brand))
            ->when($sort === 'longest', fn ($q) => $q->orderByRaw(self::SINCE . ' ASC'))
            ->when($sort === 'recent', fn ($q) => $q->orderByRaw(self::SINCE . ' DESC'))
            ->when($sort === 'name', fn ($q) => $q->orderBy('products.name'))
            ->when(!in_array($sort, ['longest', 'recent', 'name'], true), fn ($q) => $q->orderByDesc('sold_recently')->orderByRaw(self::SINCE . ' ASC'))
            ->orderBy('products.id');
    }

    public static function count(): int
    {
        return Product::where('stock', 0)->count();
    }

    private static function categoryIds(int $id): array
    {
        return Category::cachedAll()->filter(fn (Category $c) => $c->id === $id || $c->parent_id === $id)->modelKeys();
    }
}
