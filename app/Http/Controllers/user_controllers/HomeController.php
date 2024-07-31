<?php

namespace app\Http\Controllers\user_controllers;
use App\Models\Product;
use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        $product=Product::all();
       
        return view('user_views/home', ['product'=>$product]);
    }

}

