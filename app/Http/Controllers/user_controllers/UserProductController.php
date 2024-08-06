<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use Illuminate\Http\Request;

class UserProductController extends Controller
{

    public function show($id)
{
//    $productImage=ProductImage::with('images')->find($id);

    $products = Product::with('images','reviews')->find($id);
//     $product = Product::findOrFail($id);

    $product = Product::with('productOptions.option')->findOrFail($id);
//     $products = Product::with('images')->find($id);
        $ratingCount = $product->reviews()->count();
        $averageRating =5;

//    $product = Product::findOrFail($id);
    $category = Category::find($product->category_id);

    $relatedProducts = Product::where('category_id', $product->category_id)
                                  ->where('id', '!=', $id)
                                  ->with('images')
                                  ->get();


    $reviews = $product->reviews;
    return view('user_views/SingleProduct', [
        'product' => $product,
        'category' => $category,
        'products'=>$products,
        'relatedProducts' => $relatedProducts,
        'reviews' => $reviews,
        'ratingCount' => $ratingCount,
        'averageRating' => $averageRating,
//        'productO'=>$productO,
    ]);
}



public function createReview(Request $request, $productId)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'phoneNumber' => 'required|string|max:15',
        'message' => 'required|string',
        'rating' => 'required|integer|between:1,5',
    ]);

    // Create a new review
    $review = Review::create([
        'product_id' => $productId,
        'name' => $request->name,
        'phoneNumber' => $request->phoneNumber,
        'message' => $request->message,
        'rating' => $request->rating,
    ]);

    return response()->json(['success' => true, 'message' => 'Thank you for your review!', 'review' => $review]);
}


}
