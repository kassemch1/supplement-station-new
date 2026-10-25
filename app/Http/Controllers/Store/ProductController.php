<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // /products/{slug}. Older links (/products/2, a slug from before a rename, different letter case)
    // redirect permanently to the current address; an archived product sends visitors to its category.
    public function show(Request $request, string $key)
    {
        // Slugs are lowercase; /products/Some-Name finds the product and redirects to the real address
        $slug = strtolower($key);
        $product = Product::with('images', 'reviews', 'productOptions.option')->where('slug', $slug)->first();

        if (!$product || $product->slug !== $key) {
            $id = $product?->id
                ?? (ctype_digit($key) ? (int) $key : null)
                ?? Product::idForOldSlug($slug)
                ?? Product::withTrashed()->where('slug', $slug)->value('id');
            $found = $id ? Product::withTrashed()->find($id) : null;
            abort_unless($found, 404);

            if ($found->trashed()) {
                $category = Category::cachedAll()->firstWhere('id', $found->category_id);
                // Not permanent: the admin can restore the product
                return redirect($category ? $category->url() : route('shop'), 302);
            }

            $query = $request->getQueryString();

            return redirect($found->url() . ($query ? '?' . $query : ''), 301);
        }

        $reviews = $product->reviews;

        // The slider shows a few products of the same category, products in stock first
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->withCardData()
            ->orderByDesc('stock')->latest('id')
            ->limit(8)
            ->get();

        return view('user_views/SingleProduct', [
            'product' => $product,
            'products' => $product,
            'category' => Category::cachedAll()->firstWhere('id', $product->category_id),
            'relatedProducts' => $relatedProducts,
            'reviews' => $reviews,
            'ratingCount' => $reviews->count(),
            'averageRating' => $reviews->isNotEmpty() ? $reviews->avg('rating') : 5,
        ]);
    }

    public function createReview(Request $request, int $productId): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'rating' => 'required|integer|between:1,5',
        ]);

        // Only products in the shop can be reviewed (an unknown id used to fail in the database)
        $product = Product::findOrFail($productId);
        $review = $product->reviews()->create($data);

        return response()->json(['success' => true, 'message' => 'Thank you for your review!', 'review' => $review]);
    }
}
