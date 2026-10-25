    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
        }
        .header img {
            max-width: 200px;
        }
        .order-details {
            border-collapse: collapse;
            width: 100%;
        }
        .order-details th, .order-details td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .order-details th {
            background-color: #f4f4f4;
            text-align: left;
        }
        .footer {
            text-align: center;
            padding-top: 20px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <img src="{{ asset('assets/img/logo/preloader2.png') }}" alt="Supplement Station" width="150" height="150">
        <h1>Thank You for Your Order!</h1>
        <p>Hi {{$client_data['billing_first_name']}},</p>
    </div>
    <div class="content">
        <p>We have received your order and it is being processed. Here are the details:</p>
        <table class="order-details">
            <tr>
                <th>Order Number</th>
                <td>#{{$order->id}}</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>{{ now()->format('F d, Y') }}</td>
            </tr>
            <tr>
                <th>Shipping Address</th>
                <td>
                    {{$client_data['billing_address_1']}}<br>
                    {{$client_data['billing_city']}}<br>
                    Lebanon
                </td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td>Cash on Delivery</td>
            </tr>
        </table>
        <h2>Order Items</h2>
        <table class="order-details">
            <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Options</th>
            </tr>
            </thead>
            <tbody>
            @foreach($items as $item)
                <tr>
                    <td>{{$item->product->name}}</td>
                    <td>{{$item->quantity}}</td>
                    <td>{{ number_format($item->product->price_after_discount, 2) }}</td>
                    <td>
                        @foreach($item->selectedOptions() as $key => $value)
                            {{ $key }}: {{ $value }}<br>
                        @endforeach
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr>
                <th colspan="2">Subtotal</th>
                <td>${{ number_format($order->subtotal_amount, 2) }}</td>
            </tr>
            @if($order->hasCoupon())
            <tr>
                <th colspan="2">Coupon {{ $order->coupon_code }}</th>
                <td>-${{ number_format($order->coupon_discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr>
                <th colspan="2">Shipping</th>
                <td>Shipping details will be provided upon order confirmation.</td>
            </tr>
            <tr>
                <th colspan="2">Total</th>
                <td>${{ number_format($order->total_amount, 2) }}</td>
            </tr>
            </tfoot>
        </table>
    </div>
    <div class="footer">
        <p>If you have any questions about your order, please contact us at +961 81-823-038</p>
        <p>Thank you for shopping with us!</p>
        <p>Best regards,<br>Supplement Station</p>
    </div>
</div>
</body>
</html>

