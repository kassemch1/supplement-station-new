<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;



class CartController extends Controller
{
    public function Cart()
    {
        return view('user_views.Cart');
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

    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $sessionId = Session::get('session_id');
        $cart = Cart::firstOrCreate(['session_id' => $sessionId]);

        $cartItem = CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => DB::raw("quantity + {$request->quantity}"),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Product added to cart!']);
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $sessionId = Session::get('session_id');
        $cart = Cart::where('session_id', $sessionId)->first();

        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$cartItem) {
            return response()->json(['error' => 'Cart item not found'], 404);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json(['success' => true, 'message' => 'Cart updated successfully!']);
    }

    public function removeItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $sessionId = Session::get('session_id');
        $cart = Cart::where('session_id', $sessionId)->first();

        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$cartItem) {
            return response()->json(['error' => 'Cart item not found'], 404);
        }

        $cartItem->delete();

        return response()->json(['success' => true, 'message' => 'Item removed from cart']);
    }
}
