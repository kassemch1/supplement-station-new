<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers\Controller;
use App\Models\Banner;
use App\Models\Faq;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $product=Product::all();
        $banner = Banner::first();
        $faqs=Faq::all();
        return view('user_views/home', [
            'product'=>$product,
            'banner'=>$banner,
            'faqs'=>$faqs
        ]);
    }

}

