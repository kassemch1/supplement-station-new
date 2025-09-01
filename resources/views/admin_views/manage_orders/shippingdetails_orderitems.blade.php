<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Order Details - #{{ $order->id }}</title>
    <meta name="robots" content="noindex, follow"/>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/material-design-iconic-font.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/cryptocurrency-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/helper.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/style.css') }}">
    <link id="cus-style" rel="stylesheet" href="{{ asset('admin_assets/css/style-primary.css') }}">

    <style>
        /* CSS Variables for consistent theming */
        :root {
            --bg: #202334;
            --panel: #2B2F42;
            --panel-2: #25293B;
            --text: #F5F7FA;
            --muted: #AAB2C3;
            --border: rgba(255,255,255,.08);
            --accent: #3A7DFF;
            --success: #2ECC71;
            --warning: #F4B400;
            --danger: #E74C3C;
        }

        /* Dark theme base */
        body.skin-dark {
            background: var(--bg);
            color: var(--text);
        }

        /* Main layout styling */
        .order-header {
            background: var(--panel) !important;
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,.15) !important;
            color: var(--text) !important;
        }

        .status-badge {
            font-size: 14px;
            padding: 8px 16px;
            border-radius: 20px;
        }

        .info-card {
            background: var(--panel) !important;
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,.15) !important;
            color: var(--text) !important;
        }

        .info-label {
            font-weight: 600;
            color: var(--muted) !important;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 16px;
            color: var(--text) !important;
            margin-bottom: 15px;
        }

        .action-buttons {
            gap: 10px;
        }

        .quick-actions {
            position: sticky;
            top: 20px;
            background: var(--panel) !important;
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,.15) !important;
            color: var(--text) !important;
        }

        .order-items-table {
            background: var(--panel) !important;
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,.15) !important;
        }

        .order-items-table th {
            background: var(--panel-2) !important;
            font-weight: 600;
            color: var(--muted) !important;
            border: none !important;
            padding: 15px;
        }

        .order-items-table td {
            padding: 15px;
            border-top: 1px solid var(--border) !important;
            color: var(--text) !important;
            background: transparent !important;
        }

        .total-section {
            background: var(--panel-2) !important;
            border: 1px solid var(--border) !important;
            border-radius: 12px !important;
            padding: 20px;
            margin-top: 20px;
            color: var(--text) !important;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .total-row.final {
            border-top: 2px solid var(--border) !important;
            padding-top: 15px;
            margin-top: 15px;
            font-weight: bold;
            font-size: 18px;
        }

        .coupon-info {
            background: linear-gradient(135deg, #A02334 0%, black 100%) !important;
            color: white !important;
            border-radius: 12px !important;
            padding: 15px;
            margin: 15px 0;
            border: none !important;
        }

        /* Typography fixes */
        .text-muted {
            color: var(--muted) !important;
        }

        h2, h4, h5, h6 {
            color: var(--text) !important;
        }

        /* Badge styling */
        .badge-success {
            background: var(--success) !important;
            color: #0b1a0f !important;
        }

        .badge-danger {
            background: var(--danger) !important;
        }

        .badge-warning {
            background: var(--warning) !important;
            color: #1f1f1f !important;
        }

        .badge-info {
            background: var(--accent) !important;
            color: white !important;
        }

        /* Button styling */
        .btn-primary {
            background: var(--accent) !important;
            border-color: var(--accent) !important;
        }

        .btn-primary:hover {
            filter: brightness(0.9);
        }

        .btn-secondary {
            background: #444A63 !important;
            border-color: #444A63 !important;
            color: var(--text) !important;
        }

        .btn-success {
            background: var(--success) !important;
            border-color: var(--success) !important;
        }

        .btn-warning {
            background: var(--warning) !important;
            border-color: var(--warning) !important;
            color: #1f1f1f !important;
        }

        .btn-danger {
            background: var(--danger) !important;
            border-color: var(--danger) !important;
        }

        /* Order summary border fix */
        .quick-actions .mt-4.pt-3 {
            border-top: 1px solid var(--border) !important;
        }
    </style>
</head>

<body class="skin-dark">

<div class="main-wrapper">
    <!-- Header Section Start -->
    @include('partials.adminHeader')
    <!-- Header Section End -->

    <!-- Side Header Start -->
    @include('partials.adminSideBar')
    <!-- Side Header End -->

    <!-- Content Body Start -->
    <div class="content-body">

        <!-- Order Header -->
        <div class="order-header">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-2">Order #{{ $order->id }}</h2>
                    <p class="mb-0 text-muted">{{ $order->created_at->format('M d, Y \a\t h:i A')  }}</p>
                </div>
                <div class="col-md-6 text-md-right">
                    <span class="status-badge badge badge-{{ $order->status == 'pending' ? 'warning' : 'success' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                    <div class="mt-2">
                        <a href="{{ route('admin.orders.pending') }}" class="btn btn-secondary btn-sm">
                            <i class="zmdi zmdi-arrow-back"></i> Back to Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">

                <!-- Customer Information -->
                <div class="info-card">
                    <h4 class="mb-3"><i class="zmdi zmdi-account mr-2"></i>Customer Information</h4>
                    @if($order->shippingDetail)
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-label">Full Name</div>
                                <div class="info-value">{{ $order->shippingDetail->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Phone Number</div>
                                <div class="info-value">
                                    <a href="https://wa.me/{{ ltrim($order->shippingDetail->phone, '+') }}"
                                       target="_blank"
                                       class="btn btn-outline-success btn-sm">
                                        <i class="fa fa-whatsapp"></i> {{ $order->shippingDetail->phone }}
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">Email Address</div>
                                <div class="info-value">{{ $order->shippingDetail->email ?? 'Not provided' }}</div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-label">City</div>
                                <div class="info-value">{{ $order->shippingDetail->city }}</div>
                            </div>
                            <div class="col-12">
                                <div class="info-label">Delivery Address</div>
                                <div class="info-value">{{ $order->shippingDetail->address }}</div>
                            </div>
                        </div>
                    @else
                        <div class="text-center text-muted py-3">
                            <i class="zmdi zmdi-info-outline" style="font-size: 24px;"></i>
                            <p class="mb-0">No shipping details available</p>
                        </div>
                    @endif
                </div>

                <!-- Order Items -->
                <div class="info-card">
                    <h4 class="mb-3"><i class="zmdi zmdi-shopping-cart mr-2"></i>Order Items</h4>
                    <div class="d-flex justify-content-center">
                        <div class="info-card d-flex justify-content-center align-items-center" style="width: 250px; height:250px;">
                            @if($order->orderItems->count() > 0)

                                @foreach($order->orderItems as $item)
                                    @if ($item->product->images->isNotEmpty())
                                        <img src="{{ asset($item->product->images->first()->url) }}"
                                             alt="Product Image"
                                             loading="lazy"
                                             style="max-width: 100%; max-height: 100%; object-fit: cover;">
                                    @else
                                        <span>No Image</span>
                                    @endif
                        </div>
                    </div>
                    <div class="order-items-table">
                        <table class="table mb-0">
                            <thead>
                            <tr>
                                {{--                                    <th>image</th>--}}
                                <th>Product</th>
                                <th>Options</th>
                                <th>Price</th>
                                <th>Qty</th>
                                <th class="text-right">Total</th>
                            </tr>
                            </thead>
                            <tbody>

                            <tr>
                                {{--                                    <td>--}}
                                {{--                                        @if ($item->product->images->isNotEmpty())--}}
                                {{--                                            <img src="{{ asset($item->product->images->first()->url) }}" alt="Product Image" style="width: 50px; height: 50px;object-fit: cover">--}}
                                {{--                                        @else--}}
                                {{--                                            <span>No Image</span>--}}
                                {{--                                        @endif--}}
                                {{--                                    </td>--}}

                                <td><strong>{{ $item->product->name ?? 'Product not found' }}</strong></td>
                                <td>
                                    @if($item->selected_options)
                                        @php
                                            $options = json_decode($item->selected_options, true);
                                            $formattedOptions = [];
                                            foreach($options as $key => $value) {
                                                $formattedOptions[] = ucfirst($key) . ': ' . ucfirst($value);
                                            }
                                        @endphp
                                        <small class="text-muted">{{ implode(', ', $formattedOptions) }}</small>
                                    @else
                                        <small class="text-muted">No options</small>
                                    @endif
                                </td>
                                <td>${{ number_format($item->price, 2) }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td class="text-right"><strong>${{ number_format($item->price * $item->quantity, 2) }}</strong></td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Coupon Applied -->
                    @if($order->hasCoupon())
                        <div class="coupon-info">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="zmdi zmdi-local-offer mr-2"></i>
                                    <strong>{{ $order->coupon->name ?? 'Coupon Applied' }}</strong>
                                    <br>
                                    <small>Code: {{ $order->coupon_code }}</small>
                                </div>
                                <div class="text-right">
                                    <div style="font-size: 18px; font-weight: bold;">
                                        -${{ number_format($order->coupon_discount_amount, 2) }}
                                    </div>
                                    <small>{{ ucfirst(str_replace('_', ' ', $order->coupon->type ?? 'discount')) }}</small>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Order Total -->
                    <div class="total-section">
                        <div class="total-row">
                            <span>Subtotal:</span>
                            <span>${{ number_format($order->subtotal_amount, 2) }}</span>
                        </div>
                        @if($order->hasCoupon())
                            <div class="total-row">
                                <span>Coupon Discount:</span>
                                <span class="text-success">-${{ number_format($order->coupon_discount_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="total-row">
                            <span>Shipping:</span>
                            <span>TBA</span>
                        </div>
                        <div class="total-row final">
                            <span>Total Amount:</span>
                            <span>${{ number_format($order->total_amount, 2) }}</span>
                        </div>
                    </div>
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="zmdi zmdi-shopping-cart-plus" style="font-size: 48px;"></i>
                            <p class="mb-0">No items found for this order</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="quick-actions">
                    <h5 class="mb-3"><i class="zmdi zmdi-settings mr-2"></i>Quick Actions</h5>

                    <div class="mb-3">
                        <div class="info-label">Current Status</div>
                        <div class="info-value">
                            <span class="badge badge-{{ $order->status == 'pending' ? 'warning' : 'success' }}" style="font-size: 14px;">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="action-buttons d-flex flex-column">
                        @if($order->status === 'pending')
                            <button class="btn btn-success btn-lg mb-2" onclick="updateStatus('delivered')">
                                <i class="zmdi zmdi-check mr-2"></i>Mark as Delivered
                            </button>
                        @else
                            <button class="btn btn-warning btn-lg mb-2" onclick="updateStatus('pending')">
                                <i class="zmdi zmdi-refresh mr-2"></i>Mark as Pending
                            </button>
                        @endif
                    </div>

                    <!-- Order Summary -->
                    <div class="mt-4 pt-3" style="border-top: 1px solid var(--border);">
                        <h6 class="mb-3">Order Summary</h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="info-label">Items</div>
                                <div class="info-value">{{ $order->orderItems->sum('quantity') }}</div>
                            </div>
                            <div class="col-6">
                                <div class="info-label">Total</div>
                                <div class="info-value">${{ number_format($order->total_amount, 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- Content Body End -->

    <!-- Footer Section Start -->
    @include('partials.adminFooter')
    <!-- Footer Section End -->
</div>

<!-- JS -->
<script src="{{ asset('admin_assets/js/vendor/modernizr-3.6.0.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/popper.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/bootstrap.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/plugins/perfect-scrollbar.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/main.js') }}"></script>

<script>
    function updateStatus(status) {
        if (confirm('Are you sure you want to update the order status to ' + status + '?')) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: "{{ route('admin.orders.updateStatus', $order->id) }}",
                data: { status: status },
                success: function(response) {
                    console.log('Status updated successfully');
                    location.reload(); // Reload to show updated status
                },
                error: function(xhr) {
                    console.log('Full error response:', xhr);
                    if (xhr.status === 404) {
                        alert('Update route not found. Please check your routes.');
                    } else if (xhr.status === 500) {
                        alert('Server error occurred. Please check the server logs.');
                    } else {
                        alert('Error updating status: ' + (xhr.responseJSON?.message || 'Unknown error'));
                    }
                }
            });
        }
    }

    // Test if jQuery and functions are loaded
    $(document).ready(function() {
        console.log('Page loaded successfully');
        console.log('jQuery version:', $.fn.jquery);
        console.log('Update status function exists:', typeof updateStatus);
    });
</script>

</body>
</html>
