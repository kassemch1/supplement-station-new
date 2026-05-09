<?php

namespace App\Http\Controllers\user_controllers;

use App\Models\Banner;
use App\Models\Faq;
use App\Models\Plan;
use App\Models\Product;
use App\Models\OrderItem;
use App\Models\Category;
use App\Models\SpecialOffer;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;


class HomeController extends Controller
{
    public function index()
{
    $planes = Plan::all();
    $banner = Banner::first();
    $faqs = Faq::all();

    // Get top selling products based on the quantity sold in delivered order items
    $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_quantity'))
        ->whereHas('order', function ($query) {
            $query->where('status', 'delivered');
        })
        ->groupBy('product_id')
        ->orderBy('total_quantity', 'desc')
        ->limit(6)
        ->get();

    $bestSellingProductIds = $topProducts->pluck('product_id')->toArray();

    // If there are less than 6 products, fill the rest with random products
    if (count($bestSellingProductIds) < 6) {
        $randomProductIds = Product::whereNotIn('id', $bestSellingProductIds)
            ->inRandomOrder()
            ->limit(6 - count($bestSellingProductIds))
            ->pluck('id')
            ->toArray();

        $bestSellingProductIds = array_merge($bestSellingProductIds, $randomProductIds);
    }

    // Get the products for the ids collected
    $product = Product::whereIn('id', $bestSellingProductIds)->with('images')->get();

    // Get special offers (admin-managed)
    $offerProductIds = SpecialOffer::orderBy('position')->orderBy('id')
        ->limit(6)
        ->pluck('product_id')
        ->toArray();

    if (!empty($offerProductIds)) {
        $offersProducts = Product::whereIn('id', $offerProductIds)
            ->with('images')
            ->get()
            ->sortBy(function ($p) use ($offerProductIds) {
                return array_search($p->id, $offerProductIds);
            })
            ->values();
    } else {
        // Fallback: best-selling products so the section is never empty
        $offersProducts = $product;
    }

    $categories=Category::all();
    $agent=new Agent();
    return view('user_views.home', [
        'product' => $product,
        'offersProducts' => $offersProducts,
        'banner' => $banner,
        'planes' => $planes,
        'faqs' => $faqs,
        'categories'=>$categories,
        'agent'=>$agent
    ]);
}

public function test()
{
    return view('test');
}



}
