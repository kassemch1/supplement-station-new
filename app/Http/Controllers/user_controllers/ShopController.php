<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers\Controller;

class ShopController extends Controller
{
    public function Shop()
    {
        return view('user_views/Shop');

    }
}
