<?php

namespace App\Http\Controllers\admin_controllers;

use App\Http\Controllers\admin_controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Services\ImageFiles;
use App\Services\ImageOptimizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BannerController extends Controller
{
    public function index()
    {
        $banners=Banner::all();
        return view('admin_views/manage_banner/edit_banner',[
            'banners'=>$banners
        ]);
    }
    public function create()
    {
        $banner=DB::table('banners')->first();
        $products=Product::all();
        return view('admin_views/manage_banner/add_banner',[
            'products'=>$products,
            'banner'=>$banner,
        ]);
    }
    public function store(Request $request)
    {
//        dd($request->all());
        // Validate the incoming request data
        $validatedData = $request->validate([
            'image'=>'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'product_id' => 'required|integer', // Assumes product_id should be an integer
        ]);


        // Create a new product using the validated data
        $banner = Banner::first() ?: new Banner();


//        $banner->image="ssss";
        $banner->product_id = $validatedData['product_id'];

        $oldImage = null;
        if($request->hasFile('image')){
            $oldImage = $banner->image;
            $banner->image = ImageOptimizer::storeUpload($request->file('image'), 'banner_picture', ImageOptimizer::BANNER_MAX_SIZE);
        }

        $banner->save();

        // The old picture is removed only after the new one is saved, and only if nothing else uses it
        if ($oldImage && $oldImage !== $banner->image) {
            ImageFiles::forget('banners', $banner->id, $oldImage);
        }
    }
//    public function edit($id)
//    {
//        $products=Product::all();
//        $banner=Banner::find($id);
//        return view('admin_views/manage_banner/edit_banner_form',[
//            'products'=>$products,
//            'banner'=>$banner
//        ]);
//    }
//    public function update(Request $request)
//    {
////        dd($request);
//        $validatedData = $request->validate([
//            'image'=>'image:allow_svg|mimes:jpeg,png,jpg,gif,svg|max:2048',
//            'product_id' => 'required|integer', // Assumes product_id should be an integer
//        ]);
//
////        $banner =Banner::findOrFail($request->input('banner_id'));
//        $banner = Banner::firstOrFail();
//        if($request->hasFile('image')){
//            if ($banner->image) {
//                // Use Storage facade to delete the old image
//                Storage::delete(str_replace('storage/', 'public/', $banner->image));
//            }
//            $bannerPic = $request->file('image')->store('public/banner_picture');
//            $banner->image = str_replace('public/', 'storage/', $bannerPic);
//        }
//
//        $banner->product_id = $validatedData['product_id'];
//
//        $banner->save();
//        return response()->json(['success' => 'Banner updated successfully']);
//    }
//    public function destroy(Request $request)
//    {
//        $banner=Banner::findOrFail($request->id);
//        $banner->delete();
//        return response()->json(['success' => 'Banner deleted successfully']);
//    }
}
