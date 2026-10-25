<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Models\Banner;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\SpecialOffer;
use App\Services\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%' . $request->input('search') . '%'))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->input('category_id')))
            ->when($request->filled('stock'), fn ($q) => match ($request->input('stock')) {
                'in-stock' => $q->where('stock', 1),
                'archived' => $q->onlyTrashed(),
                default => $q->where('stock', 0),
            })
            ->with('images', 'getCategory')
            ->paginate(8)
            ->withQueryString();

        return view('admin_views/manage_products/edit_product', [
            'products' => $products,
            'categories' => Category::cachedAll(),
        ]);
    }

    public function create()
    {
        return view('admin_views/manage_products/add_product', ['categories' => Category::cachedAll()]);
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create($request->productData());
        $this->storeImages($product, $request->file('images', []));
    }

    public function edit(int $id)
    {
        return view('admin_views/manage_products/edit_product_form', [
            'product' => Product::with('images')->findOrFail($id),
            'categories' => Category::cachedAll(),
        ]);
    }

    public function update(ProductRequest $request): JsonResponse
    {
        $product = Product::findOrFail($request->input('product_id'));
        $product->update($request->productData());
        $this->storeImages($product, $request->file('images', []));

        return response()->json(['success' => 'Product updated successfully']);
    }

    /** @param UploadedFile[] $images */
    private function storeImages(Product $product, array $images): void
    {
        foreach ($images as $image) {
            $product->images()->create([
                'url' => ImageOptimizer::storeUpload($image, 'products_images', ImageOptimizer::PRODUCT_MAX_SIZE),
            ]);
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        $product = Product::findOrFail($request->id);

        // Archive (soft delete) so past orders keep the product. Take it out of carts, special offers
        // and the home banner so nothing on the shop links to it anymore.
        DB::transaction(function () use ($product) {
            CartItem::where('product_id', $product->id)->delete();
            SpecialOffer::where('product_id', $product->id)->delete();
            Banner::where('product_id', $product->id)->update(['product_id' => null]);
            $product->delete();
        });

        return response()->json(['success' => 'product deleted successfully']);
    }

    // Brings a deleted (archived) product back to the shop
    public function restore(int $id)
    {
        Product::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('manageProducts.index', ['stock' => 'archived'])->with('success', 'Product restored to the shop.');
    }
}
