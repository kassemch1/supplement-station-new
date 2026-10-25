<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\ProductOption;
use App\Services\ImageFiles;
use App\Services\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// A product's option values (e.g. Flavor: Vanilla) and whether each is in stock
class ProductOptionController extends Controller
{
    private const RULES = [
        'option_id' => 'required|exists:options,id',
        'option_value' => 'required|string|max:255',
        'stock' => 'boolean',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        'remove_image' => 'nullable|boolean',
    ];

    public function index()
    {
        $productOptions = ProductOption::with(['product', 'option'])->paginate(10);

        return view('admin_views/manage_product_option/edit_product_option', compact('productOptions'));
    }

    public function show(int $product_id)
    {
        $productOptions = ProductOption::where('product_id', $product_id)->with('option')->get();

        return view('admin_views/manage_product_option/edit_product_option', compact('productOptions', 'product_id'));
    }

    public function create(int $product_id)
    {
        return view('admin_views/manage_product_option/add_product_option', ['product_id' => $product_id, 'options' => Option::all()]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate(['product_id' => 'required|exists:products,id'] + self::RULES);
        ProductOption::create($this->withImage($request, $data, null));

        return response()->json(['success' => 'Product option added successfully.']);
    }

    public function edit(int $id)
    {
        return view('admin_views.manage_product_option.edit_product_option_form', [
            'productOption' => ProductOption::findOrFail($id),
            'options' => Option::all(),
        ]);
    }

    // The product a value belongs to never changes, only the value and its stock
    public function update(Request $request, int $id): JsonResponse
    {
        $productOption = ProductOption::findOrFail($id);
        $oldImage = $productOption->image;
        $productOption->update($this->withImage($request, $request->validate(self::RULES), $oldImage));

        if ($oldImage && $oldImage !== $productOption->image) {
            ImageFiles::deleteIfUnused($oldImage);
        }

        return response()->json(['success' => 'Product option updated successfully.']);
    }

    // The picture is optional: a new upload replaces it, "remove picture" clears it, otherwise it stays
    private function withImage(Request $request, array $data, ?string $current): array
    {
        unset($data['remove_image']);
        $data['image'] = $current;
        if ($request->hasFile('image')) {
            $data['image'] = ImageOptimizer::storeUpload($request->file('image'), 'product_options', ImageOptimizer::OPTION_MAX_SIZE,
                !$request->boolean('keep_background'));
        } elseif ($request->boolean('remove_image')) {
            $data['image'] = null;
        }

        return $data;
    }

    // The × on the picture in the edit form: removes it right away
    public function removeImage(int $id): JsonResponse
    {
        $productOption = ProductOption::findOrFail($id);
        $image = $productOption->image;
        $productOption->update(['image' => null]);
        // Past orders may still show this picture; the file is only removed when nothing uses it
        if ($image) {
            ImageFiles::deleteIfUnused($image);
        }

        return response()->json(['success' => 'Picture removed.', 'message' => 'Picture removed.']);
    }

    public function destroy(int $id): JsonResponse
    {
        $productOption = ProductOption::findOrFail($id);
        $image = $productOption->image;
        $productOption->delete();
        // Past orders may still show this picture; it is only removed when nothing uses it
        if ($image) {
            ImageFiles::deleteIfUnused($image);
        }

        return response()->json(['success' => 'Product option deleted successfully.']);
    }
}
