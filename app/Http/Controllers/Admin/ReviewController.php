<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    public function index(int $product_id)
    {
        $reviews = Review::where('product_id', $product_id)->get();

        return view('admin_views/manage_review/edit_review', compact('reviews', 'product_id'));
    }

    public function destroy(int $id): JsonResponse
    {
        Review::findOrFail($id)->delete();

        return response()->json(['message' => 'Review deleted successfully.']);
    }
}
