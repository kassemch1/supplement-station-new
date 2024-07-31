<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers\Controller;
use App\Models\Category;
use App\Models\Product;

class UserProductController extends Controller
{

    public function show($id)
{
    $product = Product::findOrFail($id);
    $category = Category::find($product->category_id);

    return view('user_views/SingleProduct', [
        'product' => $product,
        'category' => $category,
    ]);
}



}
