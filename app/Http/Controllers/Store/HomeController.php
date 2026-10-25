<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\OrderItem;
use App\Models\Plan;
use App\Models\Product;
use App\Models\SpecialOffer;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    private const CARDS = 6;

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
        $bestSellers = Product::cards($ids);

        // Special offers are picked by the admin; without any the best sellers are shown
        $offerIds = SpecialOffer::productIds(self::CARDS);

        return view('user_views.home', [
            'product' => $bestSellers,
            'offersProducts' => $offerIds ? Product::cards($offerIds) : $bestSellers,
            'banner' => Banner::first(),
            'planes' => Plan::all(),
            'faqs' => Faq::all(),
        ]);
    }
}
