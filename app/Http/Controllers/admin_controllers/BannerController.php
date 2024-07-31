<?php

namespace App\Http\Controllers\admin_controllers;

use App\Http\Controllers\admin_controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            'image'=>'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'product_id' => 'required|integer', // Assumes product_id should be an integer
        ]);


        // Create a new product using the validated data
        $banner = Banner::first() ?: new Banner();


//        $banner->image="ssss";
        $banner->product_id = $validatedData['product_id'];

        if($request->hasFile('image')){
            if ($banner->image) {
                // Use Storage facade to delete the old image
                Storage::delete(str_replace('storage/', 'public/', $banner->image));
            }
            $bannerPic = $request->file('image')->store('public/banner_picture');
            $banner->image = str_replace('public/', 'storage/', $bannerPic);
        }

        $banner->save();
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
//            'image'=>'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
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
