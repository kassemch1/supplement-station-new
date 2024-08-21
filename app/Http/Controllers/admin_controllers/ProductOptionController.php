<?php

namespace App\Http\Controllers\admin_controllers;


use App\Http\Controllers\user_controllers\Controller;
use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use App\Models\ProductOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductOptionController extends Controller
{
    public function index()
    {
        $productOptions = ProductOption::with(['product', 'option'])->paginate(10);
        return view('admin_views/manage_product_option/edit_product_option', compact('productOptions'));
    }
    public function show($product_id)
{
    $productOptions = ProductOption::where('product_id', $product_id)->with('option')->get();
    return view('admin_views/manage_product_option/edit_product_option', compact('productOptions', 'product_id'));
}

    public function create($product_id)
    {
        $options=Option::all();
       
        return view('admin_views/manage_product_option/add_product_option', compact('product_id', 'options'));
    }
 

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'option_id' => 'required|exists:options,id',
            'option_value' => 'required|string|max:255',
            'stock' => 'boolean',
        ]);
    
        try {
            ProductOption::create($request->all());
            return response()->json(['success' => 'Product option added successfully.']);
        } catch (\Exception $e) {
            Log::error('Failed to add product option', ['error' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to add product option.'], 500);
        }
    }
    

    public function edit($id)
    {
        $productOption = ProductOption::findOrFail($id);
        $options = Option::all();
        return view('admin_views.manage_product_option.edit_product_option_form', compact('productOption', 'options'));
    }
    
    public function update(Request $request)
{
    $request->validate([
        'id' => 'required|exists:product_options,id',
        'option_id' => 'required|exists:options,id',
        'option_value' => 'required|string|max:255',
        'stock' => 'boolean',
    ]);

    try {
        $productOption = ProductOption::findOrFail($request->id);
        $productOption->update($request->all());

        return response()->json(['success' => 'Product option updated successfully.']);
    } catch (\Exception $e) {
        Log::error('Failed to update product option', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Failed to update product option.'], 500);
    }
}


public function destroy(Request $request)
{
    $request->validate(['id' => 'required|exists:product_options,id']);
    
    try {
        ProductOption::destroy($request->id);
        return response()->json(['success' => 'Product option deleted successfully.']);
    } catch (\Exception $e) {
        Log::error('Failed to delete product option', ['error' => $e->getMessage()]);
        return response()->json(['error' => 'Failed to delete product option.'], 500);
    }
}

}


