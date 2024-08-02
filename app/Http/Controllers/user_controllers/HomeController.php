<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers\Controller;
use App\Models\Banner;
use App\Models\Product;
use App\Models\plane;
class HomeController extends Controller
{
    public function index()
    {
        $planes=plane::all();
        $product=Product::all();
        $banner = Banner::first();
        return view('user_views/home', [
            'product'=>$product,
            'banner'=>$banner,
            'planes'=>$planes,
        ]);
    }

}

