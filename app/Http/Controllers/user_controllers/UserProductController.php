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

    $product = Product::with('productOptions.option')->findOrFail($id);
    $products = Product::with('images')->find($id);
//    $product = Product::findOrFail($id);
    $category = Category::find($product->category_id);

    return view('user_views/SingleProduct', [
        'product' => $product,
        'category' => $category,
//        'productImage'=>$productImage,
        'products'=>$products,
//        'productO'=>$productO,
    ]);
}



}
