<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\ImageFiles;
use App\Services\ImageOptimizer;
use App\Services\PageCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

// Brands (BioTechUSA, Optimum Nutrition...) with their logos; products are linked to one on the product form
class BrandController extends Controller
{
    public function index()
    {
        return view('admin_views/manage_brands/index', [
            'brands' => Brand::withCount('products')->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin_views/manage_brands/form', ['brand' => new Brand()]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->save(new Brand(), $request, 'Brand added successfully.');
    }

    public function edit(int $id)
    {
        return view('admin_views/manage_brands/form', ['brand' => Brand::findOrFail($id)]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        return $this->save(Brand::findOrFail($id), $request, 'Brand updated successfully.');
    }

    private function save(Brand $brand, Request $request, string $message): JsonResponse
    {
        $data = $request->validate([
            // Case-insensitive on every database (SQLite compares names case-sensitively)
            'name' => ['required', 'string', 'max:100', function ($attribute, $value, $fail) use ($brand) {
                if (Brand::whereRaw('LOWER(name) = ?', [mb_strtolower(trim($value))])->whereKeyNot($brand->id)->exists()) {
                    $fail('A brand with this name already exists.');
                }
            }],
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_logo' => 'nullable|boolean',
        ]);

        $oldLogo = $brand->logo;
        $brand->name = $data['name'];
        $brand->sort_order = (int) ($data['sort_order'] ?? 0);
        if ($request->hasFile('logo')) {
            $brand->logo = ImageOptimizer::storeUpload($request->file('logo'), 'brands', ImageOptimizer::LOGO_MAX_SIZE);
        } elseif ($request->boolean('remove_logo')) {
            $brand->logo = null;
        }
        $brand->save();

        if ($oldLogo && $oldLogo !== $brand->logo) {
            ImageFiles::deleteIfUnused($oldLogo);
        }

        return response()->json(['success' => true, 'message' => $message, 'redirect' => route('manageBrands.index')]);
    }

    // Dragging the rows of the brand list: the ids in their new order, every brand once. They are numbered
    // 1, 2, 3... which is the order of the Brands menu and the "Shop by brand" row in the shop.
    public function reorder(Request $request): JsonResponse
    {
        $ids = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|distinct',
        ])['ids'];

        $all = Brand::pluck('id')->all();
        abort_if(count($ids) !== count($all) || array_diff($all, $ids), 422, 'The list must hold every brand once. Reload the page and try again.');

        foreach (array_values($ids) as $position => $id) {
            Brand::whereKey($id)->update(['sort_order' => $position + 1]);
        }
        // a bulk update skips the model events that clear the brand list and the stored store pages
        Brand::forgetCache();
        PageCache::flush();

        return response()->json(['success' => true, 'message' => 'Brand order saved.']);
    }

    // The × on the logo in the edit form: removes it right away
    public function removeLogo(int $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);
        $logo = $brand->logo;
        $brand->logo = null;
        $brand->save();
        if ($logo) {
            ImageFiles::deleteIfUnused($logo);
        }

        return response()->json(['success' => true, 'message' => 'Logo removed.']);
    }

    // The brand's products stay in the shop, just without a brand
    public function destroy(int $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);
        $logo = $brand->logo;
        $brand->delete();
        if ($logo) {
            ImageFiles::deleteIfUnused($logo);
        }

        return response()->json(['success' => true, 'message' => 'Brand deleted.']);
    }
}
