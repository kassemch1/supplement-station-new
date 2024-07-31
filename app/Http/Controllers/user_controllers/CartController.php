<?php

namespace app\Http\Controllers\user_controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use App\Models\Cart;
use App\Models\CartItem;
class CartController extends Controller
{
    public function Cart()
    {
        return view('user_views/Cart');

    }
    public function getCart()
    {
        $sessionId = Session::get('session_id'); // Use session ID from session
        $cart = Cart::where('session_id', $sessionId)->first();
    
        if (!$cart) {
            return response()->json(['items' => []]);
        }
    
        $items = $cart->items()->with('product')->get(); // Make sure to eager load the product
    
        return response()->json(['items' => $items]);
    }
    

}


