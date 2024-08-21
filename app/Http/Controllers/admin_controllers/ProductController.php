<?php

namespace App\Http\Controllers\admin_controllers;


use App\Http\Controllers\user_controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Review;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
{

    $query = Product::query();


    if ($request->has('search') && $request->input('search') != '') {
        $searchTerm = $request->input('search');
        $query->where('name', 'LIKE', "%$searchTerm%");
    }


    if ($request->has('category_id') && $request->input('category_id') != '') {
        $query->where('category_id', $request->input('category_id'));
    }


    if ($request->has('stock') && $request->input('stock') != '') {
        if ($request->input('stock') === 'in-stock') {
            $query->where('stock', 1);
        } else {
            $query->where('stock', 0);
        }
    }


    $products = $query->paginate(3);
    $categories = Category::all();

    return view('admin_views/manage_products/edit_product', [
        'products' => $products,
        'categories' => $categories,
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
            'stock' => 'required', // Assumes stock should be an integer
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
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

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('public/products_images');
                $imagePath = str_replace('public/', 'storage/', $imagePath);

                // Save the image path to the database (assumes you have a ProductImage model and images table)
                $productImage = new ProductImage();
                $productImage->product_id = $product->id;
                $productImage->url = $imagePath;
                $productImage->save();
            }
        }
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
        $product =Product::findOrFail($request->input('product_id'));
        // Check if the product already has images
        $hasImages = $product->images()->count() > 0;
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0.01', // Ensures price is greater than 0
            'category_id' => 'required|integer', // Assumes category_id should be an integer
            'discount' => 'required|numeric|between:0,100', // Ensures discount is between 0 and 100
            'description' => 'required|string',
            'stock' => 'required', // Assumes stock should be an integer
            'images' => $hasImages ? 'array' : 'required|array', // Make images required if no existing images
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ]);


        $product->name = $validatedData['name'];
        $product->price = $validatedData['price'];
        $product->category_id = $validatedData['category_id'];
        $product->discount = $validatedData['discount'];
        $product->description = $validatedData['description'];
        $product->stock = $validatedData['stock'] === 'in-stock';
        $product->save();

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePath = $image->store('public/products_images');
                $imagePath = str_replace('public/', 'storage/', $imagePath);

                // Save the image path to the database (assumes you have a ProductImage model and images table)
                $productImage = new ProductImage();
                $productImage->product_id = $product->id;
                $productImage->url = $imagePath;
                $productImage->save();
            }
        }
        return response()->json(['success' => 'Product updated successfully']);
    }
    public function destroy(Request $request)
    {
        $product=Product::findOrFail($request->id);
        $product->delete();
        return response()->json(['success' => 'product deleted successfully']);
    }



    public function show($product_id)
    {
        $reviews = Review::where('product_id', $product_id)->get();
        return view('admin_views/manage_review/edit_review', compact('reviews', 'product_id'));
    }

    public function destroyReview($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return response()->json(['message' => 'Review deleted successfully.']);
    }



}
