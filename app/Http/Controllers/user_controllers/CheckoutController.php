<?php

namespace App\Http\Controllers\user_controllers;



use App\Http\Controllers\user_controllers\Controller;

class CheckoutController extends Controller
{
    public function Checkout()
    {
        return view('user_views/Checkout');

    }
}
