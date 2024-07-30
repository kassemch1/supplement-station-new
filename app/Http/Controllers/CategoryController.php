<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
            'name' => 'required|string|max:255'
        ]);
        $category=new Category();
        $category->name=$validatedData['name'];
        $category->save();
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
            'name' => 'required|string|max:255'
        ]);

        $category =Category::findOrFail($request->input('category_id'));

        $category->name=$validatedData['name'];


        $category->save();
        return response()->json(['success' => 'Category updated successfully']);
    }
    public function destroy(Request $request)
    {
        $category=Category::findOrFail($request->id);
        $category->delete();
        return response()->json(['success' => 'Category deleted successfully']);
    }
}
