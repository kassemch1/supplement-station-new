<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin_views/manage_categories/edit_category', ['categories' => Category::all()]);
    }

    public function create()
    {
        return view('admin_views/manage_categories/add_category');
    }

    public function store(Request $request): JsonResponse
    {
        return $this->save(new Category(), $request, 'Category created successfully.');
    }

    public function edit(int $id)
    {
        return view('admin_views/manage_categories/edit_category_form', ['category' => Category::findOrFail($id)]);
    }

    public function update(Request $request): JsonResponse
    {
        return $this->save(Category::findOrFail($request->input('category_id')), $request, 'Category updated successfully.');
    }

    private function save(Category $category, Request $request, string $message): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);

        // Two categories with one name would share a menu entry; the page shows this as a warning
        if (Category::where('name', $request->input('name'))->whereKeyNot($category->id)->exists()) {
            return response()->json(['status' => 'exists', 'message' => 'The category name already exists.']);
        }

        $category->name = $request->input('name');
        $category->save();

        return response()->json(['status' => 'success', 'message' => $message]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $category = Category::findOrFail($request->id);

        // The database removes a category's products with it, bypassing product archiving
        $activeProducts = $category->getProducts()->count();
        if ($activeProducts > 0) {
            return response()->json([
                'message' => "This category still has {$activeProducts} product(s). Move them to another category or delete them first.",
            ], 422);
        }

        $category->delete();

        return response()->json(['success' => 'Category deleted successfully']);
    }
}
