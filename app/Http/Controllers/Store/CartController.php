<?php

namespace App\Http\Controllers\Store;

use App\Exceptions\CouponRejected;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\PlaceOrderRequest;
use App\Mail\CheckoutMail;
use App\Mail\NewOrderMail;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CartItemOption;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingDetail;
use App\Services\Catalog;
use App\Services\CurrentCart;
use App\Services\ProductAvailability;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

use function Illuminate\Support\defer;

class CartController extends Controller
{
    // Everything a cart line needs to show its picture, price and stock, in a fixed number of queries
    private const LINE_RELATIONS = ['product.images', 'product.productOptions.option'];

    public function show(Request $request)
    {
        // /cart used to answer the mini-cart script with JSON; a page cached before the move may still ask
        if ($request->ajax() || $request->wantsJson()) {
            return $this->items();
        }

        return view('user_views.Cart');
    }

    public function items(): JsonResponse
    {
        $cart = CurrentCart::get();
        if (!$cart) {
            return response()->json(['items' => [], 'summary' => $this->summary(collect())]);
        }

        $items = $cart->items()->with(self::LINE_RELATIONS)->get();

        foreach ($items as $item) {
            $options = $item->selectedOptions();
            $product = $item->product;

            $item->formatted_options = collect($options)->map(fn ($value, $name) => ucfirst($name) . ': ' . ucfirst($value))->implode(', ');
            $item->product_image = $item->image();
            $item->product_url = $product->url();
            $item->discounted_price = $product->price_after_discount;
            $item->subtotal = $item->lineTotal();
            // Tells the page if this line can't be ordered anymore (sold out since it was added)
            $item->unavailable_reason = ProductAvailability::check($product, $options)['error'];

            $product->unsetRelation('productOptions');   // only needed for the check, not by the page
        }

        return response()->json(['items' => $items, 'summary' => $this->summary($items)]);
    }

    // Totals worked out here, so the cart drawer, the cart page and the checkout always show the same
    // numbers as the order will have (the coupon is checked again when the order is placed)
    private function summary($items): array
    {
        $subtotal = round((float) $items->sum(fn (CartItem $item) => $item->lineTotal()), 2);
        $coupon = null;
        $discount = 0.0;

        if ($applied = Session::get('applied_coupon')) {
            $model = Coupon::find($applied['id']);
            $problem = !$model ? 'This coupon no longer exists.'
                : ($model->problem() ?? (!$model->canApplyToOrder($subtotal)
                    ? 'Add $' . number_format($model->minimum_order_amount - $subtotal, 2) . ' more to use this coupon (minimum $' . number_format($model->minimum_order_amount, 2) . ').'
                    : null));
            $discount = $problem || $subtotal <= 0 ? 0.0 : $model->calculateDiscount($subtotal);
            $coupon = [
                'code' => $applied['code'],
                'name' => $applied['name'] ?? $applied['code'],
                'discount_percentage' => $applied['discount_percentage'] ?? null,
                'problem' => $problem,
            ];
        }

        return [
            'count' => (int) $items->sum('quantity'),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'total' => round($subtotal - $discount, 2),
            'coupon' => $coupon,
            'has_problems' => $items->contains(fn ($item) => $item->unavailable_reason),
        ];
    }

    // "Complete your stack": products that go with what is in the cart
    public function suggestions(): JsonResponse
    {
        $ids = CurrentCart::get()?->items()->pluck('product_id')->all() ?? [];

        return response()->json(['products' => Catalog::suggestions($ids)]);
    }

    public function add(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'product_id' => 'required|integer',
            'quantity' => 'required|integer|min:1|max:100',
            'selected_options' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $selectedOptions = json_decode($request->input('selected_options') ?: '{}', true);
        $product = Product::find($request->product_id);

        // Sold-out products and options used to be hidden only by the page; the server refuses them
        $availability = ProductAvailability::check($product, $selectedOptions);
        if ($availability['error']) {
            return response()->json(['success' => false, 'message' => $availability['error']], 422);
        }

        try {
            $cart = CurrentCart::getOrCreate();

            // Same product with the same options goes on the same line
            $line = $cart->items()->where('product_id', $product->id)->get()
                ->first(fn (CartItem $item) => $item->hasOptions($selectedOptions));

            if ($line) {
                $line->update(['quantity' => min(100, $line->quantity + $request->quantity)]);
            } else {
                $line = $cart->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $request->quantity,
                    'selected_options' => json_encode($selectedOptions),
                ]);
                foreach ($availability['productOptions'] as $productOption) {
                    CartItemOption::create(['cart_item_id' => $line->id, 'product_option_id' => $productOption->id]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Error adding item to cart', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'There was an error adding the item to the cart.'], 500);
        }

        return response()->json(['success' => true, 'message' => 'Product added to cart!']);
    }

    public function update(Request $request): JsonResponse
    {
        $request->validate($this->lineRules() + ['quantity' => 'required|integer|min:1|max:100']);

        $line = $this->findLine($request);
        if (!$line) {
            return response()->json(['success' => false, 'message' => 'This item is no longer in your cart.'], 404);
        }

        $line->update(['quantity' => $request->quantity]);

        return response()->json(['success' => true, 'message' => 'Cart updated successfully!']);
    }

    public function remove(Request $request): JsonResponse
    {
        $request->validate($this->lineRules());

        $line = $this->findLine($request);
        if (!$line) {
            return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);
        }

        $line->delete();

        return response()->json(['success' => true, 'message' => 'Item removed from cart']);
    }

    private function lineRules(): array
    {
        return [
            'cart_item_id' => 'nullable|integer',
            'product_id' => 'required_without:cart_item_id|integer',
            'selected_options' => 'nullable|string',
        ];
    }

    // The exact cart line the customer clicked. Pages send cart_item_id; pages still cached in a
    // browser send product_id (+ options), which is matched as precisely as the request allows.
    private function findLine(Request $request): ?CartItem
    {
        $cart = CurrentCart::get();
        if (!$cart) {
            return null;
        }

        if ($request->filled('cart_item_id')) {
            return $cart->items()->whereKey($request->cart_item_id)->first();
        }

        $lines = $cart->items()->where('product_id', $request->product_id)->get();
        if ($request->filled('selected_options')) {
            $options = json_decode($request->selected_options, true) ?? [];

            return $lines->first(fn (CartItem $item) => $item->hasOptions($options));
        }

        return $lines->first();
    }

    public function placeOrder(PlaceOrderRequest $request): JsonResponse
    {
        // Each checkout page load carries its own token. A double-click or a retry sends the same token
        // again, and gets the order that was already placed instead of creating a duplicate.
        $checkoutToken = $request->input('checkout_token') ?: (string) Str::uuid();
        if ($existing = Order::where('checkout_token', $checkoutToken)->first()) {
            return $this->orderPlaced($existing, $request->billing_email);
        }

        $cart = CurrentCart::get()?->load(array_map(fn ($relation) => "items.$relation", self::LINE_RELATIONS));
        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty. Please add items to your cart before placing an order.',
            ], 422);
        }

        // Something may have sold out since it was added to the cart
        $problems = $cart->items
            ->map(fn (CartItem $item) => ProductAvailability::check($item->product, $item->selectedOptions())['error'])
            ->filter();
        if ($problems->isNotEmpty()) {
            return response()->json([
                'success' => false,
                'message' => $problems->implode(' ') . ' Please remove it from your cart and try again.',
            ], 422);
        }

        try {
            $order = DB::transaction(fn () => $this->createOrder($cart, $request, $checkoutToken));
        } catch (CouponRejected $e) {
            Session::forget('applied_coupon');

            return response()->json([
                'success' => false,
                'message' => "The coupon {$e->getMessage()} can no longer be used for this order and was removed. Please review your total and place the order again.",
            ], 422);
        } catch (UniqueConstraintViolationException $e) {
            // The same checkout was submitted twice at the same moment and the other request won
            if ($existing = Order::where('checkout_token', $checkoutToken)->first()) {
                return $this->orderPlaced($existing, $request->billing_email);
            }
            Log::error('Place order failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'We could not place your order. Please try again.'], 500);
        } catch (\Throwable $e) {
            Log::error('Place order failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'We could not place your order. Please try again.'], 500);
        }

        Session::forget('applied_coupon');
        $this->sendOrderEmails($order, $cart->items, $request->only('billing_first_name', 'billing_phone', 'billing_address_1', 'billing_city', 'billing_email'));

        return $this->orderPlaced($order, $request->billing_email);
    }

    private function createOrder(Cart $cart, Request $request, string $checkoutToken): Order
    {
        $subtotal = $cart->subtotal();
        $coupon = null;
        $couponDiscount = 0;

        if ($applied = Session::get('applied_coupon')) {
            // Locked until this order is saved, so two orders can't both use a single-use coupon
            $coupon = Coupon::lockForUpdate()->find($applied['id']);
            if (!$coupon || !$coupon->isValid() || !$coupon->canApplyToOrder($subtotal)) {
                throw new CouponRejected($applied['code']);
            }
            $couponDiscount = $coupon->calculateDiscount($subtotal);
            $coupon->apply();
        }

        $order = Order::create([
            'subtotal_amount' => $subtotal,
            'total_amount' => $subtotal - $couponDiscount,
            'coupon_id' => $coupon?->id,
            'coupon_code' => $coupon?->code,
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

        // Name, picture and price are copied, so the order stays as it was if the product changes
        OrderItem::insert($cart->items->map(fn (CartItem $item) => [
            'order_id' => $order->id,
            'product_id' => $item->product_id,
            'product_name' => $item->product->name,
            'product_image' => $item->image(),
            'quantity' => $item->quantity,
            'price' => $item->product->price_after_discount,
            'selected_options' => $item->selected_options,
            'created_at' => $order->created_at,
            'updated_at' => $order->created_at,
        ])->all());

        $cart->items()->delete();

        return $order;
    }

    // Sent after the response: a slow or failing mail server can never make a placed order look failed
    private function sendOrderEmails(Order $order, $items, array $customer): void
    {
        defer(function () use ($order, $items, $customer) {
            if ($customer['billing_email'] ?? null) {
                try {
                    Mail::to($customer['billing_email'])->send(new CheckoutMail($customer, $items, $order));
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
    }

    private function orderPlaced(Order $order, ?string $billingEmail): JsonResponse
    {
        return response()->json([
            'success' => true,
            'billing_email' => $billingEmail,
            'order_id' => $order->id,
        ]);
    }
}
