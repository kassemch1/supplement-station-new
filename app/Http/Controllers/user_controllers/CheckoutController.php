<?php

namespace app\Http\Controllers\user_controllers;

use App\Http\Controllers\Controller;

class CheckoutController extends Controller
{
    public function Checkout()
    {
        return view('user_views/Checkout');

    }
}
