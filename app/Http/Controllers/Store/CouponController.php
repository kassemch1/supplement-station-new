<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Services\CurrentCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

// The coupon box on the cart and checkout pages. The applied coupon lives in the session and is
// checked again (locked) when the order is placed.
class CouponController extends Controller
{
    public function apply(Request $request): JsonResponse
    {
        $request->validate(['coupon_code' => 'required|string|max:255']);

        $coupon = Coupon::where('code', strtoupper(trim($request->input('coupon_code'))))->first();
        if (!$coupon) {
            return $this->refuse('Invalid coupon code.');
        }
        if ($problem = $coupon->problem()) {
            return $this->refuse($problem);
        }
        if (Session::has('applied_coupon')) {
            return $this->refuse('Please remove the existing coupon before applying a new one.');
        }

        $cartTotal = $this->cartTotal();
        if ($cartTotal <= 0) {
            return $this->refuse('Your cart is empty.');
        }
        if (!$coupon->canApplyToOrder($cartTotal)) {
            return $this->refuse('Minimum order amount of $' . number_format($coupon->minimum_order_amount, 2) . ' required to use this coupon.');
        }

        $discount = $coupon->calculateDiscount($cartTotal);
        $details = [
            'code' => $coupon->code,
            'name' => $coupon->name,
            'discount_percentage' => $coupon->discount_percentage,
            'discount_amount' => $discount,
        ];
        Session::put('applied_coupon', ['id' => $coupon->id, 'type' => $coupon->type] + $details);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied successfully! You saved $' . number_format($discount, 2),
            'coupon' => $details,
            'cart_total' => $cartTotal,
            'final_total' => $cartTotal - $discount,
        ]);
    }

    public function remove(): JsonResponse
    {
        if (!Session::has('applied_coupon')) {
            return $this->refuse('No coupon is currently applied.');
        }

        Session::forget('applied_coupon');
        $cartTotal = $this->cartTotal();

        return response()->json([
            'success' => true,
            'message' => 'Coupon removed successfully.',
            'cart_total' => $cartTotal,
            'final_total' => $cartTotal,
        ]);
    }

    public function applied(): JsonResponse
    {
        $coupon = Session::get('applied_coupon');
        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'No coupon applied.']);
        }

        return response()->json(['success' => true, 'coupon' => $coupon]);
    }

    private function cartTotal(): float
    {
        return CurrentCart::get()?->load('items.product')->subtotal() ?? 0;
    }

    private function refuse(string $message): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message], 400);
    }
}
