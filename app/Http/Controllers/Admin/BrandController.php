<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Services\ImageFiles;
use App\Services\ImageOptimizer;
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
