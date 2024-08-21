<?php

namespace App\Http\Controllers\user_controllers;

use App\Models\Banner;
use App\Models\Faq;
use App\Models\Product;
use App\Models\plane;
use App\Models\OrderItem;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Jenssegers\Agent\Agent;


class HomeController extends Controller
{
    public function index()
{
    $planes = Plane::all();
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

    // Get offers products
    $offersCategory = Category::where('name', 'Offers')->first();
    $offersProducts = [];

    if ($offersCategory) {
        $offersProducts = Product::where('category_id', $offersCategory->id)
                                 ->take(6)
                                 ->with('images')
                                 ->get();

        // If no products found in offers category, fall back to best-selling products
        if ($offersProducts->isEmpty()) {
            $offersProducts = $product; // Use best-selling products as a fallback
        }
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
