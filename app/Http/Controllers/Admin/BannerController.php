<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Services\ImageFiles;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;

// The single home page banner: a picture linking to one product
class BannerController extends Controller
{
    public function index()
    {
        return view('admin_views/manage_banner/edit_banner', ['banners' => Banner::with('getProduct')->get()]);
    }

    public function create()
    {
        return view('admin_views/manage_banner/add_banner', [
            'products' => Product::get(['id', 'name']),
            'banner' => Banner::first(),
        ]);
    }

    public function store(Request $request)
    {
        $banner = Banner::first() ?: new Banner();
        $data = $request->validate([
            // The first banner needs a picture; after that the admin may change only the product
            'image' => ($banner->exists ? 'nullable|' : 'required|') . 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $banner->product_id = $data['product_id'];

        $oldImage = null;
        if ($request->hasFile('image')) {
            $oldImage = $banner->image;
            $banner->image = ImageOptimizer::storeUpload($request->file('image'), 'banner_picture', ImageOptimizer::BANNER_MAX_SIZE);
        }

        $banner->save();

        // The old picture is removed only after the new one is saved, and only if nothing else uses it
        if ($oldImage && $oldImage !== $banner->image) {
            ImageFiles::forget('banners', $banner->id, $oldImage);
        }
    }
}
