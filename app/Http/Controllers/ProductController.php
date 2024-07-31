<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products=Product::all();
        return view('admin_views/manage_products/edit_product',[
            'products'=>$products
        ]);
    }
    public function create()
    {
        $categories=Category::all();
        return view('admin_views/manage_products/add_product',[
            'categories'=>$categories
        ]);
    }
    public function store(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01', // Ensures price is greater than 0
            'category_id' => 'required|integer', // Assumes category_id should be an integer
            'discount' => 'required|numeric|between:0,100', // Ensures discount is between 0 and 100
            'description' => 'required|string',
            'stock' => 'required' // Assumes stock should be an integer
        ]);


        // Create a new product using the validated data
        $product = new Product();
        $product->name = $validatedData['name'];
        $product->price = $validatedData['price'];
        $product->category_id = $validatedData['category_id'];
        $product->discount = $validatedData['discount'];
        $product->description = $validatedData['description'];
        $product->stock = $validatedData['stock'] === 'in-stock';
        $product->save();
    }
    public function edit($id)
    {
        $categories=Category::all();
        $product=Product::find($id);
        return view('admin_views/manage_products/edit_product_form',[
            'product'=>$product,
            'categories'=>$categories
        ]);
    }
    public function update(Request $request)
    {
//        dd($request);
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01', // Ensures price is greater than 0
            'category_id' => 'required|integer', // Assumes category_id should be an integer
            'discount' => 'required|numeric|between:0,100', // Ensures discount is between 0 and 100
            'description' => 'required|string',
            'stock' => 'required' // Assumes stock should be an integer
        ]);

        $product =Product::findOrFail($request->input('product_id'));

        $product->name = $validatedData['name'];
        $product->price = $validatedData['price'];
        $product->category_id = $validatedData['category_id'];
        $product->discount = $validatedData['discount'];
        $product->description = $validatedData['description'];
        $product->stock = $validatedData['stock'] === 'in-stock';
        $product->save();
        return response()->json(['success' => 'Product updated successfully']);
    }
    public function destroy(Request $request)
    {
        $product=Product::findOrFail($request->id);
        $product->delete();
        return response()->json(['success' => 'product deleted successfully']);
    }

    public function show($id)
{
    $product = Product::findOrFail($id);
    $category = Category::find($product->category_id); 

    return view('user_views/SingleProduct', [
        'product' => $product,
        'category' => $category,
    ]);
}


    public function getall()
    {
        $product=Product::all();
       
        return view('user_views/home', compact('product'));
    }
}
