<?php

namespace App\Http\Controllers\user_controllers;



use App\Http\Controllers\user_controllers\Controller;
use App\Models\Category;

class CheckoutController extends Controller
{
    public function Checkout()
    {
        $categories=Category::all();
        return view('user_views/Checkout',[
            'categories'=>$categories
        ]);

    }
}
