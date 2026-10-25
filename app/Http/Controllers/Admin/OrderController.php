<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    private const HEADINGS = ['pending' => 'Pending Orders', 'delivered' => 'Delivered Orders', 'cancelled' => 'Cancelled Orders'];

    // /admin/orders/pending, /delivered and /cancelled
    public function index(Request $request, string $status)
    {
        $orders = Order::where('status', $status)
            ->orderBy('created_at', $request->input('sort') === 'asc' ? 'asc' : 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin_views/manage_orders/order_list', [
            'orders' => $orders,
            'status' => $status,
            'heading' => self::HEADINGS[$status],
        ]);
    }

    public function show(int $orderId)
    {
        // Older order lines have no saved picture and fall back to the product's (display_image)
        $order = Order::with(['orderItems' => fn ($q) => $q->orderBy('id'), 'orderItems.product.images', 'shippingDetail', 'coupon'])
            ->findOrFail($orderId);

        return view('admin_views/manage_orders/shippingdetails_orderitems', ['order' => $order]);
    }

    public function updateStatus(Request $request, int $orderId)
    {
        $request->validate(['status' => ['required', Rule::in(Order::STATUSES)]]);

        Order::findOrFail($orderId)->update(['status' => $request->input('status')]);

        return back()->with('success', 'Order status updated successfully');
    }

    public function destroy(int $orderId)
    {
        Order::findOrFail($orderId)->delete();

        return back()->with('success', 'Order deleted successfully');
    }
}
