<?php

namespace app\Http\Controllers\user_controllers;

use App\Http\Controllers\Controller;

class SingleProductController extends Controller
{
    public function singleProduct()
    {
        return view('user_views/SingleProduct');
    }
}
