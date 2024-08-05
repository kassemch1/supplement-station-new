<?php

namespace App\Http\Controllers\user_controllers;

use App\Models\Banner;
use App\Models\Faq;
use App\Models\Product;
use App\Models\Plane;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;


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
            ->limit(5)
            ->get();

        $productIds = $topProducts->pluck('product_id')->toArray();

        // If there are less than 4 products, fill the rest with random products
        if (count($productIds) < 5) {
            $randomProductIds = Product::whereNotIn('id', $productIds)
                ->inRandomOrder()
                ->limit(5 - count($productIds))
                ->pluck('id')
                ->toArray();

            $productIds = array_merge($productIds, $randomProductIds);
        }

        // Get the products for the ids collected
        $product = Product::whereIn('id', $productIds)->get();

        return view('user_views.home', [
            'product' => $product,
            'banner' => $banner,
            'planes' => $planes,
            'faqs' => $faqs,
        ]);
    }
}
