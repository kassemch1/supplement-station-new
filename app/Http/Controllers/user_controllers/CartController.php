<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers\Controller;
use App\Mail\CheckoutMail;
use App\Mail\ContactMail;
use App\Mail\NewOrderMail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartItemOption;
use App\Models\Category;
use App\Models\Coupon; // ADD THIS LINE - This was missing!
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ShippingDetail;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use function Illuminate\Support\defer;

class CartController extends Controller
{
    public function Cart()
    {
        $categories=Category::all();
        return view('user_views.Cart',[
            'categories'=>$categories
        ]);
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

            // Calculate the price after discount if available
            $discount = $item->product->discount;
            $price = $item->product->price;
            $item->discounted_price = $discount ? $price * (1 - ($discount / 100)) : $price; // Apply discount
            $item->subtotal = $item->discounted_price * $item->quantity; // Calculate subtotal with discount
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

    public function removeItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $sessionId = Session::get('session_id');
        $cart = Cart::where('session_id', $sessionId)->first();

        if (!$cart) {
            return response()->json(['success' => false, 'message' => 'Cart not found'], 404);
        }

        $cartItem = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $request->product_id)
            ->first();

        if (!$cartItem) {
            return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }

        $cartItem->delete();

        return response()->json(['success' => true, 'message' => 'Item removed from cart']);
    }

    public function placeOrder(Request $request)
    {
        $request->validate([
            'billing_first_name' => 'required|string',
            'billing_phone' => 'required|phone:LB',
            'billing_address_1' => 'required|string',
            'billing_city' => 'required|string',
            'billing_email' => 'nullable|email',
            'checkout_token' => 'nullable|string|max:64',
        ], [
            'billing_phone.phone' => 'The phone number must be a valid Lebanese phone number.',
        ]);

        // Each checkout page load carries its own token. A double-click or a retry sends the same token
        // again, and gets the order that was already placed instead of creating a duplicate.
        $checkoutToken = $request->input('checkout_token') ?: (string) Str::uuid();
        if ($existing = Order::where('checkout_token', $checkoutToken)->first()) {
            return $this->orderPlacedResponse($existing, $request->billing_email);
        }

        $sessionId = Session::get('session_id');
        $cart = Cart::where('session_id', $sessionId)->first();
        $client_data = $request->only('billing_first_name', 'billing_phone', 'billing_address_1', 'billing_city','billing_email');

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty. Please add items to your cart before placing an order.',
            ], 422);
        }

        DB::beginTransaction();
    
        try {
            $subtotalAmount = $cart->items->sum(function ($item) {
                $discount = $item->product->discount;
                $price = $item->product->price;
                $discountedPrice = $discount ? $price * (1 - ($discount / 100)) : $price; // Apply discount
                return $discountedPrice * $item->quantity;
            });
    
            // Handle coupon application
            $appliedCoupon = Session::get('applied_coupon');
            $couponDiscount = 0;
            $couponUsed = null;
    
            if ($appliedCoupon) {
                $coupon = Coupon::find($appliedCoupon['id']);
                
                if ($coupon && $coupon->isValid() && $coupon->canApplyToOrder($subtotalAmount)) {
                    $couponDiscount = $coupon->calculateDiscount($subtotalAmount);
                    $coupon->apply(); // This will increment usage and deactivate single-use coupons
                    $couponUsed = $coupon;
                }
                
                // Clear the applied coupon from session
                Session::forget('applied_coupon');
            }
    
            $finalAmount = $subtotalAmount - $couponDiscount;
    
            $order = Order::create([
                'subtotal_amount' => $subtotalAmount,
                'total_amount' => $finalAmount,
                'coupon_id' => $couponUsed ? $couponUsed->id : null,
                'coupon_code' => $couponUsed ? $couponUsed->code : null,
                'coupon_discount_amount' => $couponDiscount,
                'status' => 'pending',
                'checkout_token' => $checkoutToken,
            ]);
    
            ShippingDetail::create([
                'order_id' => $order->id,
                'name' => $request->billing_first_name,
                'phone' => $request->billing_phone,
                'email' => $request->billing_email,
                'address' => $request->billing_address_1,
                'city' => $request->billing_city,
            ]);
    
            foreach ($cart->items as $cartItem) {
                $discount = $cartItem->product->discount;
                $price = $cartItem->product->price;
                $discountedPrice = $discount ? $price * (1 - ($discount / 100)) : $price;
    
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'product_name' => $cartItem->product->name,
                    'product_image' => $cartItem->product->images->first()?->url,
                    'quantity' => $cartItem->quantity,
                    'price' => $discountedPrice,
                    'selected_options' => $cartItem->selected_options,
                ]);
            }
    
            $cart->items()->delete();

            DB::commit();
        } catch (UniqueConstraintViolationException $e) {
            // The same checkout was submitted twice at the same moment and the other request won
            DB::rollBack();
            $existing = Order::where('checkout_token', $checkoutToken)->first();
            if ($existing) {
                return $this->orderPlacedResponse($existing, $request->billing_email);
            }
            Log::error('Place order failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'We could not place your order. Please try again.'], 500);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Place order failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'We could not place your order. Please try again.'], 500);
        }

        // The order is saved at this point. Emails go out after the response is sent, so a slow or
        // failing mail server can never make a placed order look like it failed.
        $items = $cart->items;
        $billingEmail = $request->billing_email;
        defer(function () use ($billingEmail, $client_data, $items, $order) {
            if ($billingEmail) {
                try {
                    Mail::to($billingEmail)->send(new CheckoutMail($client_data, $items, $order));
                } catch (\Throwable $e) {
                    Log::error("Order #{$order->id}: customer receipt email failed", ['error' => $e->getMessage()]);
                }
            }
            try {
                Mail::to(config('mail.admin_address'))->send(new NewOrderMail($order->load('orderItems', 'shippingDetail')));
            } catch (\Throwable $e) {
                Log::error("Order #{$order->id}: admin notification email failed", ['error' => $e->getMessage()]);
            }
        });

        return $this->orderPlacedResponse($order, $request->billing_email);
    }

    private function orderPlacedResponse(Order $order, ?string $billingEmail)
    {
        return response()->json([
            'success' => true,
            'billing_email' => $billingEmail,
            'order_id' => $order->id,
        ]);
    }
}