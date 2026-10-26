<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Faq;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\SpecialOffer;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    // Products in each row of cards (the rows scroll sideways)
    private const CARDS = 10;

    public function index()
    {
        // Best sellers: most units in delivered orders, among products still in the shop
        $ids = OrderItem::select('product_id')
            ->whereHas('order', fn ($query) => $query->where('status', 'delivered'))
            ->whereIn('product_id', Product::select('id'))
            ->groupBy('product_id')
            ->orderByDesc(DB::raw('SUM(quantity)'))
            ->limit(self::CARDS)
            ->pluck('product_id')
            ->all();

        // A young shop with few sales fills the row with random products
        if (count($ids) < self::CARDS) {
            $ids = array_merge($ids, Product::whereNotIn('id', $ids)->inRandomOrder()->limit(self::CARDS - count($ids))->pluck('id')->all());
        }
        $bestSellers = $this->inStockFirst(Product::cards($ids));

        // Special offers are picked by the admin; without any the best sellers are shown
        $offerIds = SpecialOffer::productIds(self::CARDS);

        return view('user_views.home', [
            'product' => $bestSellers,
            'offersProducts' => $offerIds ? $this->inStockFirst(Product::cards($offerIds)) : $bestSellers,
            'banner' => Banner::first(),
            'faqs' => Faq::all(),
            // Menus and lists come from the cache (one query counts the products per category)
            'categoryTree' => Category::treeWithProducts(),
            'brands' => Brand::cachedAll(),
        ]);
    }

    // Sold-out products go to the end of a row; the rest keep their order
    private function inStockFirst($products)
    {
        return $products->sortBy(fn (Product $product) => $product->stock ? 0 : 1)->values();
    }
}
