<?php

namespace App\Http\Controllers\user_controllers;


use App\Models\Product;
use App\Models\Category;
use Jenssegers\Agent\Agent;

class ShopController extends Controller
{

    public function Shop()
    {


        $agent = new Agent();
        $categories = Category::all();
        $search = request()->query('search');
        $orderBy = request()->query('orderby');
        $categoryName = request()->query('category');
        $page = request()->query('page', 1); // Default to page 1

        $productQuery = Product::query()->with('images');

        if ($categoryName) {
            $category = Category::where('name', $categoryName)->first();
            if (!$category) {
                return response()->json(['error' => 'Category not found.'], 404);
            }
            $productQuery->where('category_id', $category->id);
        }

        if ($search) {
            $productQuery->where('name', 'like', '%' . $search . '%');
        }

        // Calculate the discounted price and apply sorting
        $productQuery->selectRaw('*, (price - (price * discount / 100)) as discounted_price');

        if ($orderBy === 'price') {
            $productQuery->orderBy('discounted_price', 'asc'); // Ascending order
        } elseif ($orderBy === 'price-desc') {
            $productQuery->orderBy('discounted_price', 'desc'); // Descending order
        }

        // Apply pagination
        $products = $productQuery->paginate(6, ['*'], 'page', $page);

        // Get offers products
        $offersCategory = Category::where('name', 'Offers')->first();
        $offersProducts = [];

        if ($offersCategory) {
            $offersProducts = Product::where('category_id', $offersCategory->id)
                                     ->take(4)
                                     ->with('images')
                                     ->get();


            if ($offersProducts->isEmpty()) {
                $offersProducts = Product::inRandomOrder()
                                         ->take(4)
                                         ->with('images')
                                         ->get();
            }
        } else {

            $offersProducts = Product::inRandomOrder()
                                     ->take(4)
                                     ->with('images')
                                     ->get();
        }


        if (request()->ajax()) {
            return response()->json([
                'product' => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ],

            ]);
        }

        return view('user_views.Shop', [
            'product' => $products,
            'categories' => $categories,
            'selectedCategory' => $categoryName,
            'offersProducts'=>$offersProducts,
            'agent'=>$agent,
        ]);
    }
}
