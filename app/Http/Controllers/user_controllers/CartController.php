<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartItemOption;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ShippingDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;


class CartController extends Controller
{
    public function Cart()
    {
        return view('user_views.Cart');
    }

    public function getCart() {
        $sessionId = Session::get('session_id');
        $cart = Cart::where('session_id', $sessionId)->first();
    
        if (!$cart) {
            return response()->json(['items' => []]);
        }
    
        $items = $cart->items()->with('product.images')->get();
    
        foreach ($items as $item) {
            $selectedOptions = json_decode($item->selected_options, true);
            $formattedOptions = [];
    
            foreach ($selectedOptions as $optionName => $optionValue) {
                $formattedOptions[] = ucfirst($optionName) . ': ' . ucfirst($optionValue);
            }
    
            $item->formatted_options = implode(', ', $formattedOptions);
            $item->product_image = $item->product->images->first()->url ?? null; // Assuming you want the first image
        }
    
        return response()->json(['items' => $items]);
    }
    


    public function addToCart(Request $request)
    {
        try{
        // Validate the request inputs
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'selected_options' => 'nullable|string', // Accept as string initially
        ]);

        // Decode the JSON string
        $selectedOptions = json_decode($request->input('selected_options'), true)?? [];

        // Check if the decoded value is an array
        if (!is_array($selectedOptions)) {
            return response()->json(['error' => 'Selected options must be an array.'], 400);
        }

        // Ensure both options are selected
        $product = Product::findOrFail($request->product_id);
        $requiredOptions = ProductOption::where('product_id', $product->id)->pluck('option_id')->unique();

            if (count($selectedOptions) !== $requiredOptions->count() && $requiredOptions->count() > 0) {
                return response()->json(['error' => 'All required options must be selected.'], 400);
            }

        $sessionId = Session::get('session_id');
        $cart = Cart::firstOrCreate(['session_id' => $sessionId]);

        // Convert selectedOptions to a JSON string for storage
        $optionsJson = json_encode($selectedOptions);

        // Find existing cart items with the same product
        $cartItems = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->get();

        $cartItem = null;

        foreach ($cartItems as $item) {
            // Get the selected options for the current cart item
            $itemOptions = json_decode($item->selected_options, true) ?? [];

            // Compare the options arrays
            if ($itemOptions == $selectedOptions) {
                $cartItem = $item;
                break;
            }
        }

        if ($cartItem) {
            // Update quantity if item exists
            $cartItem->increment('quantity', $request->quantity);
        } else {
            // Create new cart item
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'selected_options' => $optionsJson,
            ]);

            // Attach options to the cart item
            foreach ($selectedOptions as $optionName => $optionValue) {
                $productOption = ProductOption::where('product_id', $request->product_id)
                    ->where('option_value', $optionValue)
                    ->first();

                if ($productOption) {
                    CartItemOption::create([
                        'cart_item_id' => $cartItem->id,
                        'product_option_id' => $productOption->id,
                    ]);
                }
            }
        }

            return response()->json(['success' => true, 'message' => 'Product added to cart!']);
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors
            return response()->json(['success' => false, 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error adding item to cart: ' . $e->getMessage());

            // Return a general error response
            return response()->json(['success' => false, 'message' => 'There was an error adding the item to the cart.'], 500);
        }
    }

    public function updateCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'selected_options' => 'nullable|string',
        ]);

        $sessionId = Session::get('session_id');
        $cart = Cart::where('session_id', $sessionId)->first();

        if (!$cart) {
            return response()->json(['error' => 'Cart not found'], 404);
        }

        // Decode the JSON string
        $selectedOptions = json_decode($request->input('selected_options'), true) ?? [];

        // Find existing cart items with the same product
        $cartItems = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->get();

        $cartItem = null;

        foreach ($cartItems as $item) {
            // Get the selected options for the current cart item
            $itemOptions = json_decode($item->selected_options, true) ?? [];

            // Compare the options arrays
            if ($itemOptions == $selectedOptions) {
                $cartItem = $item;
                break;
            }
        }

        if ($cartItem) {
            // Update quantity if item exists
            $cartItem->quantity = $request->quantity;
            $cartItem->save();
        } else {
            // Create new cart item if not found
            $cartItem = CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'selected_options' => $request->input('selected_options'),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Cart updated successfully!']);
    }

//    protected function updateCartItemOptions($cartItemId, $selectedOptions)
//    {
//        // Clear existing options for this cart item
//        CartItemOption::where('cart_item_id', $cartItemId)->delete();
//
//        foreach ($selectedOptions as $optionName => $optionValue) {
//            $productOption = ProductOption::whereHas('option', function($query) use ($optionName) {
//                $query->where('option_name', $optionName);
//            })
//                ->where('option_value', $optionValue)
//                ->first();
//
//            if ($productOption) {
//                CartItemOption::create([
//                    'cart_item_id' => $cartItemId,
//                    'product_option_id' => $productOption->id,
//                    'created_at' => now(),
//                    'updated_at' => now(),
//                ]);
//            } else {
//                // Log or handle cases where the product option is not found
//
//            }
//        }
//    }

//    public function updateCart(Request $request)
//    {
//        try {
//            $request->validate([
//                'product_id' => 'required|exists:products,id',
//                'quantity' => 'required|integer|min:1',
//                'selected_options' => 'nullable|string',
//            ]);
//
//            $selectedOptions = json_decode($request->input('selected_options'), true)?? [];
//
//            // Check if the decoded value is an array
//            if (!is_array($selectedOptions)) {
//                return response()->json(['error' => 'Selected options must be an array.'], 400);
//            }
//
//
//            $sessionId = Session::get('session_id');
//            $cart = Cart::where('session_id', $sessionId)->first();
//
//            if (!$cart) {
//                return response()->json(['error' => 'Cart not found'], 404);
//            }
//
//
//            $optionsJson = json_encode($selectedOptions);
//            // Find cart item based on product_id and selected_options
//            $cartItem = CartItem::where('cart_id', $cart->id)
//                ->where('product_id', $request->product_id)
//                ->where('selected_options', json_encode($optionsJson))
//                ->first();
//
//            if (!$cartItem) {
//                return response()->json(['error' => 'Cart item not found'], 404);
//            }
//
//            // Update quantity
//            $cartItem->quantity = $request->quantity;
//            $cartItem->save();
//
//
//            return response()->json([
//                'success' => true,
//                'message' => 'Cart updated successfully!',
//
//            ]);
//        } catch (\Illuminate\Validation\ValidationException $e) {
//            return response()->json(['success' => false, 'message' => $e->errors()], 422);
//        } catch (\Exception $e) {
//            Log::error('Error updating cart: ' . $e->getMessage());
//            return response()->json(['success' => false, 'message' => 'There was an error updating the cart.'], 500);
//        }
//    }

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
                    'selected_options' => $cartItem->selected_options, // Store the selected options
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
