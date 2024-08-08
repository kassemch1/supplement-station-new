<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers\Controller;
use App\Models\Product;
use App\Models\Category;

class ShopController extends Controller
{public function Shop($categoryName = null)
    {
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
    
        if ($orderBy === 'price') {
            $productQuery->orderByRaw('(price - (price * discount / 100)) asc');
        } elseif ($orderBy === 'price-desc') {
            $productQuery->orderByRaw('(price - (price * discount / 100)) desc');
        }
    
        // Apply pagination
        $products = $productQuery->paginate(6, ['*'], 'page', $page);
    
        if (request()->ajax()) {
            return response()->json([
                'product' => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ],
            ]);
        }
    
        return view('user_views/Shop', [
            'product' => $products,
            'categories' => $categories,
            'selectedCategory' => $categoryName,
        ]);
    }
    
}
