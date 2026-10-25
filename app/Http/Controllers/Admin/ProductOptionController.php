<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\ProductOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// A product's option values (e.g. Flavor: Vanilla) and whether each is in stock
class ProductOptionController extends Controller
{
    private const RULES = [
        'option_id' => 'required|exists:options,id',
        'option_value' => 'required|string|max:255',
        'stock' => 'boolean',
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
        ProductOption::create($request->validate(['product_id' => 'required|exists:products,id'] + self::RULES));

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
        ProductOption::findOrFail($id)->update($request->validate(self::RULES));

        return response()->json(['success' => 'Product option updated successfully.']);
    }

    public function destroy(int $id): JsonResponse
    {
        ProductOption::findOrFail($id)->delete();

        return response()->json(['success' => 'Product option deleted successfully.']);
    }
}
