<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductImage;
use App\Services\ImageFiles;
use App\Services\PageCache;
use Illuminate\Http\Request;

class ProductImagesController extends Controller
{
    public function destroy(Request $request, int $id)
    {
        $image = ProductImage::findOrFail($id);

        // A product always keeps at least one picture (the shop and the product form need one)
        if (ProductImage::where('product_id', $image->product_id)->count() <= 1) {
            return $request->wantsJson()
                ? response()->json(['message' => 'A product needs at least one picture. Add another one first.'], 422)
                : back()->with('error', 'A product needs at least one picture.');
        }

        $image->delete();
        // Removes the file too, unless an order line (or anything else) still shows it
        ImageFiles::forget('product_images', $image->id, $image->url);

        // The edit page removes the picture in place; a plain form post goes back to the page
        return $request->wantsJson()
            ? response()->json(['success' => 'Image deleted successfully'])
            : back()->with('success', 'Image deleted successfully');
    }

    // The order the admin dragged the pictures into: ids, first = main picture
    public function reorder(Request $request, int $productId)
    {
        $product = Product::findOrFail($productId);
        $ids = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ])['ids'];

        $own = $product->images()->pluck('id')->all();
        abort_if(count($ids) !== count($own) || array_diff($own, $ids), 422, 'The list must hold every picture of this product once.');

        foreach (array_values($ids) as $position => $id) {
            ProductImage::where('id', $id)->update(['sort_order' => $position + 1]);
        }
        // A bulk update skips the model events that refresh the stored store pages
        PageCache::flush();

        return response()->json(['success' => 'Order saved']);
    }
}
