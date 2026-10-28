<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Order</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
<h2>New order #{{ $order->id }}</h2>
<p><strong>Name:</strong> {{ $order->shippingDetail?->name }}</p>
<p><strong>Phone:</strong> {{ $order->shippingDetail?->phone }}</p>
<p><strong>Email:</strong> {{ $order->shippingDetail?->email ?: '-' }}</p>
@if($order->isPickup())
<p><strong>Delivery:</strong> Pick up from the store</p>
@else
<p><strong>Address:</strong> {{ $order->shippingDetail?->address }}, {{ $order->shippingDetail?->city }}</p>
@endif

<table cellpadding="6" cellspacing="0" border="1" style="border-collapse: collapse; border-color: #ddd;">
    <tr>
        <th align="left">Product</th>
        <th align="left">Options</th>
        <th>Qty</th>
        <th align="right">Price</th>
    </tr>
    @foreach($order->orderItems as $item)
        <tr>
            <td>{{ $item->display_name }}</td>
            <td>
                @foreach(json_decode($item->selected_options, true) ?: [] as $name => $value)
                    {{ $name }}: {{ $value }}<br>
                @endforeach
            </td>
            <td align="center">{{ $item->quantity }}</td>
            <td align="right">${{ number_format($item->price * $item->quantity, 2) }}</td>
        </tr>
    @endforeach
</table>

<p>
    Subtotal: {{ $order->formatted_subtotal }}<br>
    @if($order->hasCoupon())
        Coupon {{ $order->coupon_code }}: -{{ $order->formatted_discount }}<br>
    @endif
    <strong>Total: {{ $order->formatted_total }}</strong>
</p>

<p><a href="{{ route('admin.orders.details', $order->id) }}">Open the order in the admin panel</a></p>
</body>
</html>
