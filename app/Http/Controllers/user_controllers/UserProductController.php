<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;

class UserProductController extends Controller
{

    public function show($id)
    {
        // Sold-out products used to redirect to the home page; the page now shows them as sold out
        $product = Product::with('images', 'reviews', 'productOptions.option')->findOrFail($id);
        $reviews = $product->reviews;
        $ratingCount = $reviews->count();
        $averageRating = $ratingCount > 0 ? $reviews->avg('rating') : 5;

        // The slider shows a few products of the same category (it used to load the whole category),
        // products in stock first
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with('images')->withCount('reviews')->withAvg('reviews', 'rating')
            ->orderByDesc('stock')->latest('id')
            ->limit(8)
            ->get();

        return view('user_views/SingleProduct', [
            'product' => $product,
            'products' => $product,
            'category' => Category::cachedAll()->firstWhere('id', $product->category_id),
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'ratingCount' => $ratingCount,
            'averageRating' => $averageRating,
        ]);
    }

public function createReview(Request $request, $productId)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'message' => 'required|string',
        'rating' => 'required|integer|between:1,5',
    ]);

    // Create a new review
    $review = Review::create([
        'product_id' => $productId,
        'name' => $request->name,
        'message' => $request->message,
        'rating' => $request->rating,
    ]);

    return response()->json(['success' => true, 'message' => 'Thank you for your review!', 'review' => $review]);
}


}
