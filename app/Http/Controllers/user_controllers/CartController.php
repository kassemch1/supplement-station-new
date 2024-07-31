<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingDetail;
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
        $sessionId = Session::get('session_id');
        $cart = Cart::where('session_id', $sessionId)->first();

        if (!$cart) {
            return response()->json(['items' => []]);
        }

        $items = $cart->items()->with('product')->get();

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

    public function placeOrder(Request $request)
    {
        $request->validate([
            'billing_first_name' => 'required|string',
            'billing_phone' => 'required|numeric',
            'billing_address_1' => 'required|string',
            'billing_city' => 'required|string',
        ]);
    
        $sessionId = Session::get('session_id');
        $cart = Cart::where('session_id', $sessionId)->first();
        
        if (!$cart || $cart->items->isEmpty()) {
            return redirect()->back()->with('error', 'Your cart is empty. Please add items to your cart before placing an order.');
        }
        
        DB::beginTransaction();
    
        try {
            $order = Order::create([
                'total_amount' => $cart->items->sum(function ($item) {
                    return $item->product->price * $item->quantity;
                }),
                'status' => 'pending',
            ]);
    
            ShippingDetail::create([
                'order_id' => $order->id,
                'name' => $request->billing_first_name,
                'phone' => $request->billing_phone,
                'address' => $request->billing_address_1,
                'city' => $request->billing_city,
            ]);
    
            foreach ($cart->items as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                ]);
            }

            $cart->items()->delete();
    
            DB::commit();
    
            return redirect()->route('checkout')->with('success', 'Order placed successfully!');
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return response()->json(['error' => 'Database error: ' . $e->getMessage()], 500);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'General error: ' . $e->getMessage()], 500);
        }
    }
    
}
