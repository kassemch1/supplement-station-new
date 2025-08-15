<?php

namespace App\Http\Controllers\user_controllers;

use App\Http\Controllers\user_controllers\Controller;
use App\Models\Coupon;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;

class CouponController extends Controller
{
    /**
     * Apply a coupon code to the cart
     */
    public function apply(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string|max:255',
        ]);

        $couponCode = strtoupper(trim($request->input('coupon_code')));
        
        // Find the coupon
        $coupon = Coupon::where('code', $couponCode)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code.'
            ], 400);
        }

        // Check if coupon is valid (now timezone aware)
        if (!$coupon->isValid()) {
            $message = 'This coupon is no longer valid.';
            
            if (!$coupon->is_active) {
                $message = 'This coupon has been deactivated.';
            } elseif ($coupon->isExpired()) {
                $beirutTime = Carbon::now('Asia/Beirut')->format('M d, Y h:i A');
                $expiryTime = $coupon->expires_at->setTimezone('Asia/Beirut')->format('M d, Y h:i A');
                $message = "This coupon expired on {$expiryTime} (Beirut time). Current time: {$beirutTime}";
            } elseif ($coupon->type === 'reusable' && $coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
                $message = 'This coupon has reached its maximum usage limit.';
            }
            
            return response()->json([
                'success' => false,
                'message' => $message
            ], 400);
        }

        // Check if a coupon is already applied
        $existingCoupon = Session::get('applied_coupon');
        if ($existingCoupon) {
            return response()->json([
                'success' => false,
                'message' => 'Please remove the existing coupon before applying a new one.'
            ], 400);
        }

        // Get cart total
        $cartTotal = $this->getCartTotal();
        
        if ($cartTotal <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.'
            ], 400);
        }

        // Check minimum order amount
        if (!$coupon->canApplyToOrder($cartTotal)) {
            return response()->json([
                'success' => false,
                'message' => "Minimum order amount of $" . number_format($coupon->minimum_order_amount, 2) . " required to use this coupon."
            ], 400);
        }

        // Calculate discount
        $discountAmount = $coupon->calculateDiscount($cartTotal);
        $finalTotal = $cartTotal - $discountAmount;

        // Store coupon in session
        Session::put('applied_coupon', [
            'id' => $coupon->id,
            'code' => $coupon->code,
            'name' => $coupon->name,
            'discount_percentage' => $coupon->discount_percentage,
            'discount_amount' => $discountAmount,
            'type' => $coupon->type,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Coupon applied successfully! You saved $" . number_format($discountAmount, 2),
            'coupon' => [
                'code' => $coupon->code,
                'name' => $coupon->name,
                'discount_percentage' => $coupon->discount_percentage,
                'discount_amount' => $discountAmount,
            ],
            'cart_total' => $cartTotal,
            'final_total' => $finalTotal,
        ]);
    }

    /**
     * Remove applied coupon
     */
    public function remove()
    {
        $appliedCoupon = Session::get('applied_coupon');
        
        if (!$appliedCoupon) {
            return response()->json([
                'success' => false,
                'message' => 'No coupon is currently applied.'
            ], 400);
        }

        Session::forget('applied_coupon');
        $cartTotal = $this->getCartTotal();

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully.',
            'cart_total' => $cartTotal,
            'final_total' => $cartTotal,
        ]);
    }

    /**
     * Get current cart total
     */
    private function getCartTotal(): float
    {
        $sessionId = Session::get('session_id');
        $cart = Cart::where('session_id', $sessionId)->first();

        if (!$cart) {
            return 0;
        }

        $items = $cart->items()->with('product')->get();
        $total = 0;

        foreach ($items as $item) {
            $price = $item->product->discount ? 
                $item->product->price * (1 - ($item->product->discount / 100)) : 
                $item->product->price;
            
            $total += $price * $item->quantity;
        }

        return $total;
    }

    /**
     * Get applied coupon details
     */
    public function getAppliedCoupon()
    {
        $appliedCoupon = Session::get('applied_coupon');
        
        if (!$appliedCoupon) {
            return response()->json(['success' => false, 'message' => 'No coupon applied.']);
        }

        return response()->json([
            'success' => true,
            'coupon' => $appliedCoupon,
        ]);
    }
}