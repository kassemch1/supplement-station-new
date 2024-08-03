<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;

class UserProductController extends Controller
{

    public function show($id)
{
//    $productImage=ProductImage::with('images')->find($id);

    $products = Product::with('images')->find($id);
    $product = Product::findOrFail($id);
    $category = Category::find($product->category_id);

    $relatedProducts = Product::where('category_id', $product->category_id)
                                  ->where('id', '!=', $id)
                                  ->with('images')
                                  ->get();

    return view('user_views/SingleProduct', [
        'product' => $product,
        'category' => $category,
        'products'=>$products,
        'relatedProducts' => $relatedProducts,
    ]);
}



}
