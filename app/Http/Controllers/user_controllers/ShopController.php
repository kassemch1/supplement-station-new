<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers\Controller;
use App\Models\Product;
use App\Models\Category;
class ShopController extends Controller
{
    public function Shop($categoryName = null)
    {
        $categories = Category::all();
        $search = request()->query('search');
        $orderBy = request()->query('orderby');
        $page = request()->query('page', 1);
    
        $query = Product::query();
    
        if ($categoryName) {
            $category = Category::where('name', $categoryName)->first();
            if (!$category) {
                return response()->json(['error' => 'Category not found.'], 404);
            }
            $query->where('category_id', $category->id);
        }
    
        if ($search) {
            $query->where('name', 'like', '%' . $search . '%');
        }
    
        if ($orderBy === 'price') {
            $query->orderBy('price', 'asc');
        } elseif ($orderBy === 'price-desc') {
            $query->orderBy('price', 'desc');
        }
    
        $products = $query->paginate(12, ['*'], 'page', $page);
    
        if (request()->ajax()) {
            return response()->json([
                'product' => $products->items(),
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'total' => $products->total(),
                ]
            ]);
        }
    
        return view('user_views/Shop', [
            'product' => $products->items(),
            'categories' => $categories,
            'selectedCategory' => $categoryName,
            'pagination' => $products->links()->toHtml()
        ]);
    }
    
    
}
