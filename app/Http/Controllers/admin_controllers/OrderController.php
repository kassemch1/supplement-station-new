<?php

namespace App\Http\Controllers\admin_controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingDetail;
use Illuminate\Support\Facades\Redirect;

class OrderController extends Controller
{
    public function pendingOrders()
    {
        $pendingOrders = Order::where('status', 'pending')->paginate(4);

        return view('admin_views/manage_orders/Pending_orderList', [
            'pendingOrders' => $pendingOrders,
        ]);
    }

    public function deliveredOrders()
    {
        $deliveredOrders = Order::where('status', 'delivered')->paginate(4);

        return view('admin_views/manage_orders/Delivered_orderList', [
            'deliveredOrders' => $deliveredOrders,
        ]);
    }

    public function orderDetails($orderId)
    {
        $order = Order::findOrFail($orderId);
        $orderItems = OrderItem::where('order_id', $orderId)->with('product.images')->get();
        $shippingDetail = ShippingDetail::where('order_id', $orderId)->first();

        return view('admin_views/manage_orders/shippingdetails_orderitems', [
            'order' => $order,
            'orderItems' => $orderItems,
            'shippingDetail' => $shippingDetail
        ]);
    }
        
    public function updateStatus(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->status = $request->input('status');
        $order->save();

        return Redirect::back()->with('success', 'Order status updated successfully');
    }

    public function deleteOrder($orderId)
    {
        $order = Order::findOrFail($orderId);
        $order->delete();

        return Redirect::back()->with('success', 'Order deleted successfully');
    }

}