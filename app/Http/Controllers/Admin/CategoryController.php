<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\ImageFiles;
use App\Services\ImageOptimizer;
use App\Support\Icons;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $counts = DB::table('products')->whereNull('deleted_at')->groupBy('category_id')->pluck(DB::raw('COUNT(*)'), 'category_id');

        // Main categories, each followed by its subcategories
        return view('admin_views/manage_categories/edit_category', [
            'categories' => Category::tree()->flatMap(fn ($main) => collect([$main])->merge($main->children)),
            'productCounts' => $counts,
        ]);
    }

    public function create()
    {
        return view('admin_views/manage_categories/add_category', ['mainCategories' => $this->mainCategories()]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->save(new Category(), $request, 'Category created successfully.');
    }

    public function edit(int $id)
    {
        return view('admin_views/manage_categories/edit_category_form', [
            'category' => Category::findOrFail($id),
            'mainCategories' => $this->mainCategories(),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        return $this->save(Category::findOrFail($request->input('category_id')), $request, 'Category updated successfully.');
    }

    private function mainCategories()
    {
        return Category::whereNull('parent_id')->orderBy('sort_order')->orderBy('name')->get();
    }

    private function save(Category $category, Request $request, string $message): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            // Two levels only: a subcategory goes inside a main category, never inside another subcategory
            'parent_id' => ['nullable', 'integer', Rule::exists('categories', 'id')->whereNull('parent_id'), Rule::notIn(array_filter([$category->id]))],
            'icon' => ['nullable', 'string', Rule::in(array_keys(Icons::ALL))],
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_image' => 'nullable|boolean',
        ], [
            'parent_id.exists' => 'A subcategory can only go inside a main category.',
            'parent_id.not_in' => 'A category cannot go inside itself.',
        ]);

        // Two categories with one name would share a menu entry; the page shows this as a warning
        if (Category::where('name', $request->input('name'))->whereKeyNot($category->id)->exists()) {
            return response()->json(['status' => 'exists', 'message' => 'The category name already exists.']);
        }
        if ($request->filled('parent_id') && $category->exists && Category::where('parent_id', $category->id)->exists()) {
            return response()->json(['status' => 'exists', 'message' => 'This category has subcategories, so it has to stay a main category.']);
        }

        $oldImage = $category->image;
        $category->name = $request->input('name');
        $category->parent_id = $request->input('parent_id') ?: null;
        $category->icon = $request->input('icon') ?: null;
        $category->sort_order = (int) $request->input('sort_order', 0);
        if ($request->hasFile('image')) {
            $category->image = ImageOptimizer::storeUpload($request->file('image'), 'categories', ImageOptimizer::CATEGORY_MAX_SIZE);
        } elseif ($request->boolean('remove_image')) {
            $category->image = null;
        }
        $category->save();

        if ($oldImage && $oldImage !== $category->image) {
            ImageFiles::deleteIfUnused($oldImage);
        }

        return response()->json(['status' => 'success', 'message' => $message]);
    }

    // The × on the picture in the edit form: removes it right away (the icon shows again)
    public function removeImage(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $image = $category->image;
        $category->image = null;
        $category->save();
        if ($image) {
            ImageFiles::deleteIfUnused($image);
        }

        return response()->json(['status' => 'success', 'message' => 'Picture removed.']);
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
        $subcategories = Category::where('parent_id', $category->id)->count();
        if ($subcategories > 0) {
            return response()->json([
                'message' => "This category has {$subcategories} subcategory(ies). Move or delete them first.",
            ], 422);
        }

        $image = $category->image;
        $category->delete();
        if ($image) {
            ImageFiles::deleteIfUnused($image);
        }

        return response()->json(['success' => 'Category deleted successfully']);
    }
}
