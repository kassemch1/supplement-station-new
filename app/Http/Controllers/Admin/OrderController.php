<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    private const HEADINGS = ['pending' => 'Pending Orders', 'delivered' => 'Delivered Orders', 'cancelled' => 'Cancelled Orders'];

    // /admin/orders/pending, /delivered and /cancelled, with ?search= (order number, phone or name)
    public function index(Request $request, string $status)
    {
        $search = trim((string) $request->input('search'));

        $orders = Order::where('status', $status)
            ->search($search)
            ->with('shippingDetail')
            ->orderBy('created_at', $request->input('sort') === 'asc' ? 'asc' : 'desc')
            ->paginate(10)
            ->withQueryString();

        // nothing here: say where the search does find orders
        $elsewhere = [];
        if ($search !== '' && $orders->isEmpty()) {
            $elsewhere = Order::search($search)->where('status', '!=', $status)
                ->selectRaw('status, COUNT(*) as n')->groupBy('status')->pluck('n', 'status')->all();
        }

        return view('admin_views/manage_orders/order_list', [
            'orders' => $orders,
            'status' => $status,
            'heading' => self::HEADINGS[$status],
            'search' => $search,
            'elsewhere' => $elsewhere,
            'headings' => self::HEADINGS,
        ]);
    }

    // Polled by the bell in the admin header (admin-ui.js): the pending count, the latest pending
    // orders, and the orders placed after ?after=<id> so a new one rings once.
    public function pendingSummary(Request $request)
    {
        $zone = config('app.display_timezone');
        $card = fn (Order $order) => [
            'id' => $order->id,
            'total' => '$' . number_format((float) $order->total_amount, 2),
            'name' => $order->shippingDetail?->name ?? 'Customer',
            'city' => $order->isPickup() ? 'Store pickup' : $order->shippingDetail?->city,
            'status' => $order->status,
            'time' => $order->created_at?->timezone($zone)->format('M d, H:i'),
            'ago' => $order->created_at?->diffForHumans(),
            'url' => route('admin.orders.details', $order->id),
        ];

        $after = $request->integer('after');
        $newOrders = $after > 0
            ? Order::with('shippingDetail')->where('id', '>', $after)->orderByDesc('id')->limit(5)->get()->map($card)
            : collect();

        return response()->json([
            'pending' => Order::where('status', 'pending')->count(),
            'latest_id' => (int) Order::max('id'),
            'latest_pending' => Order::with('shippingDetail')->where('status', 'pending')->latest('id')->limit(6)->get()->map($card),
            'new_orders' => $newOrders,
            // a page left open past the session (kept signed in) takes the new session's form token from here
            'csrf' => csrf_token(),
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
