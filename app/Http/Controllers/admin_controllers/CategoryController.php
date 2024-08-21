<?php

namespace App\Http\Controllers\admin_controllers;
use App\Http\Controllers\admin_controllers\Controller;
use App\Models\Category;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories=Category::all();
        return view('admin_views/manage_categories/edit_category',[
            'categories'=>$categories
        ]);
    }
    public function create()
    {
        return view('admin_views/manage_categories/add_category');
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $existingCategory = Category::where('name', $validatedData['name'])->first();

        if ($existingCategory) {
            return response()->json([
                'status' => 'exists',
                'message' => 'The category name already exists.',
            ]);
        }

        $category = new Category();
        $category->name = $validatedData['name'];
        $category->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Category created successfully.',
        ]);
    }


    public function edit($id)
    {
        $category=Category::find($id);
        return view('admin_views/manage_categories/edit_category_form',[
            'category'=>$category
        ]);
    }
    public function update(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $category = Category::findOrFail($request->input('category_id'));

        // Check if another category with the same name exists
        $existingCategory = Category::where('name', $validatedData['name'])
            ->where('id', '!=', $category->id)
            ->first();

        if ($existingCategory) {
            return response()->json([
                'status' => 'exists',
                'message' => 'The category name already exists.',
            ]);
        }

        $category->name = $validatedData['name'];
        $category->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Category updated successfully.',
        ]);
    }

    public function destroy(Request $request)
    {
        $category=Category::findOrFail($request->id);
        $category->delete();
        return response()->json(['success' => 'Category deleted successfully']);
    }
}
