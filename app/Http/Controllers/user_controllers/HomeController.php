<?php

namespace app\Http\Controllers\user_controllers;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('user_views/home');
    }
}

