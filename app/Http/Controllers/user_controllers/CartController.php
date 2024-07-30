<?php

namespace app\Http\Controllers\user_controllers;

use App\Http\Controllers\Controller;

class CartController extends Controller
{
    public function Cart()
    {
        return view('user_views/Cart');

    }
}
