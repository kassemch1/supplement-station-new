{{--<!DOCTYPE html>--}}
{{--<html lang="en">--}}
{{--<head>--}}
{{--    <meta charset="UTF-8">--}}
{{--    <meta name="viewport" content="width=device-width, initial-scale=1.0">--}}
{{--    <title>Order Confirmation</title>--}}
{{--    <style>--}}
{{--        body {--}}
{{--            font-family: Arial, sans-serif;--}}
{{--            line-height: 1.6;--}}
{{--            color: #333;--}}
{{--            margin: 0;--}}
{{--            padding: 0;--}}
{{--        }--}}
{{--        .container {--}}
{{--            width: 80%;--}}
{{--            margin: 0 auto;--}}
{{--            padding: 20px;--}}
{{--        }--}}
{{--        .header {--}}
{{--            text-align: center;--}}
{{--            padding-bottom: 20px;--}}
{{--        }--}}
{{--        .header img {--}}
{{--            max-width: 200px;--}}
{{--        }--}}
{{--        .order-details {--}}
{{--            border-collapse: collapse;--}}
{{--            width: 100%;--}}
{{--        }--}}
{{--        .order-details th, .order-details td {--}}
{{--            border: 1px solid #ddd;--}}
{{--            padding: 8px;--}}
{{--        }--}}
{{--        .order-details th {--}}
{{--            background-color: #f4f4f4;--}}
{{--            text-align: left;--}}
{{--        }--}}
{{--        .footer {--}}
{{--            text-align: center;--}}
{{--            padding-top: 20px;--}}
{{--            font-size: 0.9em;--}}
{{--        }--}}
{{--    </style>--}}
{{--</head>--}}
{{--<body>--}}
{{--<div class="container">--}}
{{--    <div class="header">--}}
{{--        <img src="{{ $logoUrl }}" alt="Company Logo">--}}
{{--        <h1>Thank You for Your Order!</h1>--}}
{{--        <p>Hi {{ $customerName }},</p>--}}
{{--    </div>--}}
{{--    <div class="content">--}}
{{--        <p>We have received your order and it is being processed. Here are the details:</p>--}}
{{--        <table class="order-details">--}}
{{--            <tr>--}}
{{--                <th>Order Number</th>--}}
{{--                <td>{{ $order->order_number }}</td>--}}
{{--            </tr>--}}
{{--            <tr>--}}
{{--                <th>Date</th>--}}
{{--                <td>{{ $order->created_at->format('F d, Y') }}</td>--}}
{{--            </tr>--}}
{{--            <tr>--}}
{{--                <th>Shipping Address</th>--}}
{{--                <td>--}}
{{--                    {{ $order->shipping_address }}<br>--}}
{{--                    {{ $order->shipping_city }}, {{ $order->shipping_state }} {{ $order->shipping_zip }}<br>--}}
{{--                    {{ $order->shipping_country }}--}}
{{--                </td>--}}
{{--            </tr>--}}
{{--            <tr>--}}
{{--                <th>Payment Method</th>--}}
{{--                <td>{{ $order->payment_method }}</td>--}}
{{--            </tr>--}}
{{--        </table>--}}
{{--        <h2>Order Items</h2>--}}
{{--        <table class="order-details">--}}
{{--            <thead>--}}
{{--            <tr>--}}
{{--                <th>Item</th>--}}
{{--                <th>Quantity</th>--}}
{{--                <th>Price</th>--}}
{{--            </tr>--}}
{{--            </thead>--}}
{{--            <tbody>--}}
{{--            @foreach($order->items as $item)--}}
{{--                <tr>--}}
{{--                    <td>{{ $item->name }}</td>--}}
{{--                    <td>{{ $item->quantity }}</td>--}}
{{--                    <td>${{ number_format($item->price, 2) }}</td>--}}
{{--                </tr>--}}
{{--            @endforeach--}}
{{--            </tbody>--}}
{{--            <tfoot>--}}
{{--            <tr>--}}
{{--                <th colspan="2">Subtotal</th>--}}
{{--                <td>${{ number_format($order->subtotal, 2) }}</td>--}}
{{--            </tr>--}}
{{--            <tr>--}}
{{--                <th colspan="2">Shipping</th>--}}
{{--                <td>${{ number_format($order->shipping_cost, 2) }}</td>--}}
{{--            </tr>--}}
{{--            <tr>--}}
{{--                <th colspan="2">Total</th>--}}
{{--                <td>${{ number_format($order->total, 2) }}</td>--}}
{{--            </tr>--}}
{{--            </tfoot>--}}
{{--        </table>--}}
{{--    </div>--}}
{{--    <div class="footer">--}}
{{--        <p>If you have any questions about your order, please contact us at {{ $contactEmail }}.</p>--}}
{{--        <p>Thank you for shopping with us!</p>--}}
{{--        <p>Best regards,<br>{{ $companyName }}</p>--}}
{{--    </div>--}}
{{--</div>--}}
{{--</body>--}}
{{--</html>--}}
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
        <img src="https://via.placeholder.com/200x100?text=Company+Logo" alt="Company Logo">
        <h1>Thank You for Your Order!</h1>
        <p>Hi John Doe,</p>
    </div>
    <div class="content">
        <p>We have received your order and it is being processed. Here are the details:</p>
        <table class="order-details">
            <tr>
                <th>Order Number</th>
                <td>123456789</td>
            </tr>
            <tr>
                <th>Date</th>
                <td>August 5, 2024</td>
            </tr>
            <tr>
                <th>Shipping Address</th>
                <td>
                    123 Fitness Lane<br>
                    Muscle City, FC 56789<br>
                    USA
                </td>
            </tr>
            <tr>
                <th>Payment Method</th>
                <td>Credit Card</td>
            </tr>
        </table>
        <h2>Order Items</h2>
        <table class="order-details">
            <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Price</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>Whey Protein</td>
                <td>2</td>
                <td>$50.00</td>
            </tr>
            <tr>
                <td>Creatine</td>
                <td>1</td>
                <td>$25.00</td>
            </tr>
            </tbody>
            <tfoot>
            <tr>
                <th colspan="2">Subtotal</th>
                <td>$75.00</td>
            </tr>
            <tr>
                <th colspan="2">Shipping</th>
                <td>$10.00</td>
            </tr>
            <tr>
                <th colspan="2">Total</th>
                <td>$85.00</td>
            </tr>
            </tfoot>
        </table>
    </div>
    <div class="footer">
        <p>If you have any questions about your order, please contact us at support@example.com.</p>
        <p>Thank you for shopping with us!</p>
        <p>Best regards,<br>Supplement Station</p>
    </div>
</div>
</body>
</html>

