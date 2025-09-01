<!doctype html>
<html lang="en">

<head>
    <!--========= Required meta tags =========-->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Checkout | Supplement Station</title>

    <link rel="shortcut icon" href="{{ asset('assets/img/logo/preloader2.png') }}" type="images/x-icon"/>
    
    <!-- css include -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/swiper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">

    <style>
        .coupon-section {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
        }

        .coupon-input-group {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .coupon-input {
            flex: 1;
            min-width: 200px;
            padding: 10px 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        .coupon-input:focus {
            outline: none;
            border-color: #A02334;
            box-shadow: 0 0 0 0.2rem rgba(160, 35, 52, 0.25);
        }

        .coupon-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .coupon-btn.apply {
            background: #A02334;
            color: white;
        }

        .coupon-btn.apply:hover {
            background: #8a1e2b;
        }

        .coupon-btn.remove {
            background: #6c757d;
            color: white;
        }

        .applied-coupon-card {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }

        .discount-row {
            background: #e8f5e8;
            border-left: 4px solid #28a745;
        }

        .discount-row th, .discount-row td {
            color: #155724 !important;
            font-weight: 600;
        }

        .message-container {
            padding: 12px;
            margin: 10px 0;
            border-radius: 5px;
            font-size: 14px;
            display: none;
        }

        .message-container.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-container.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @media (max-width: 991px) {
            .coupon-input-group {
                flex-direction: column;
                align-items: stretch;
            }
            
            .coupon-input {
                min-width: auto;
                margin-bottom: 10px;
            }
        }
    </style>
</head>

<body>

<!-- backtotop - start -->
<div class="xb-backtotop">
    <a href="#" class="scroll">
        <i class="far fa-arrow-up"></i>
    </a>
</div>

@include('partials/navBar',['categories'=>$categories])

<div class="body-overlay"></div>

<!-- main area start -->
<main>
    <!-- breadcrumb start -->
    <section class="breadcrumb position-bottom bg_img" data-background="{{ asset('assets/img/bg/shop-cart-banner.webp')}}">
        <div class="container">
            <div class="breadcrumb__content text-center">
                <h2 class="breadcrumb__title" style="text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7); color: #fff;">Checkout</h2>
                <ul class="breadcrumb__list clearfix">
                    <li class="breadcrumb-item">
                        <a href="{{route('home')}}" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); color: #fff;">
                            Supplement Station
                        </a>
                    </li>
                    <li class="breadcrumb-item" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); color: #fff;">
                        Checkout
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- start checkout-section -->
    <section class="checkout-section pt-115 pb-385">
        <div class="container">
            <div class="row">
                <div class="col col-xs-12">
                    <div class="woocommerce">
                        <form id="checkout_form" name="checkout" method="post" class="checkout woocommerce-checkout" action="{{ route('place.order') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="col2-set" id="customer_details">
                                <div class="coll-1">
                                    <div class="woocommerce-billing-fields">
                                        <h3>Billing Details</h3>

                                        <p class="form-row">
                                            <label for="billing_first_name">Name <abbr class="required" title="required">*</abbr></label>
                                            <input type="text" name="billing_first_name" id="billing_first_name" placeholder="Name" required style="border: 1px solid #ccc; padding: 0.5rem; width: 100%; border-radius: 4px;" />
                                            <span class="error-message" id="billing_first_name_error" style="color: red; font-weight: bold"></span>
                                        </p>

                                        <p class="form-row">
                                            <label for="billing_phone">Phone <abbr class="required" title="required">*</abbr></label>
                                            <input type="tel" name="billing_phone" id="billing_phone" placeholder="Phone Number" required style="border: 1px solid #ccc; padding: 0.5rem; width: 100%; border-radius: 4px;" />
                                            <span class="error-message" id="billing_phone_error" style="color: red; font-weight: bold"></span>
                                        </p>

                                        <p class="form-row">
                                            <label for="billing_email">Email (<abbr class="optional">optional</abbr>)</label>
                                            <input type="email" name="billing_email" id="billing_email" placeholder="Email" style="border: 1px solid #ccc; padding: 0.5rem; width: 100%; border-radius: 4px;" />
                                            <small class="form-text text-muted" style="color: #dc3545 !important;">Note: Providing your email is optional, but recommended to receive your receipt.</small>
                                            <span class="error-message" id="billing_email_error" style="color: red; font-weight: bold"></span>
                                        </p>

                                        <p class="form-row">
                                            <label for="billing_address_1">Address <abbr class="required" title="required">*</abbr></label>
                                            <input type="text" name="billing_address_1" id="billing_address_1" placeholder="Street address" required style="border: 1px solid #ccc; padding: 0.5rem; width: 100%; border-radius: 4px;" />
                                            <span class="error-message" id="billing_address_1_error" style="color: red; font-weight: bold"></span>
                                        </p>

                                        <p class="form-row">
                                            <label for="billing_city">Town / City <abbr class="required" title="required">*</abbr></label>
                                            <input type="text" name="billing_city" id="billing_city" placeholder="City" required style="border: 1px solid #ccc; padding: 0.5rem; width: 100%; border-radius: 4px;" />
                                            <span class="error-message" id="billing_city_error" style="color: red; font-weight: bold"></span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <h3 id="order_review_heading">Your order</h3>

                            <div id="order_review" class="woocommerce-checkout-review-order">
                                <table class="shop_table woocommerce-checkout-review-order-table">
                                    <thead>
                                        <tr>
                                            <th class="product-name">Product</th>
                                            <th class="product-total">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Cart items will be appended here -->
                                    </tbody>
                                    <tfoot>
                                        <tr class="cart-subtotal">
                                            <th>Subtotal</th>
                                            <td>
                                                <span class="woocommerce-Price-amount amount">
                                                    <span class="woocommerce-Price-currencySymbol">$</span><span id="checkout-subtotal">0.00</span>
                                                </span>
                                            </td>
                                        </tr>
                                        
                                        <!-- Coupon Code Section -->
                                        <tr>
                                            <td colspan="2">
                                                <div class="coupon-section">
                                                    <h6 style="margin-bottom: 15px; color: #A02334;">
                                                        <i class="fa fa-ticket" style="margin-right: 8px;"></i>
                                                        Have a Coupon Code?
                                                    </h6>
                                                    <div class="coupon-input-group">
                                                        <input type="text" id="coupon-code" placeholder="Enter your coupon code..." class="coupon-input">
                                                        <button type="button" id="apply-coupon" class="coupon-btn apply">Apply</button>
                                                        <button type="button" id="remove-coupon" class="coupon-btn remove" style="display: none;">Remove</button>
                                                    </div>
                                                    <div id="coupon-message" class="message-container"></div>
                                                    <div id="applied-coupon" style="display: none;">
                                                        <div class="applied-coupon-card">
                                                            <h6><i class="fa fa-check-circle"></i> Coupon Applied!</h6>
                                                            <p><strong id="coupon-name"></strong> - <span id="coupon-discount"></span> discount</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Discount Amount (if coupon applied) -->
                                        <tr class="discount-row" style="display: none;">
                                            <th>
                                                <i class="fa fa-tag" style="margin-right: 5px;"></i>
                                                Coupon Discount
                                            </th>
                                            <td>-$<span id="discount-amount">0.00</span></td>
                                        </tr>

                                        <tr class="order-total" style="background: #f8f9fa; font-size: 18px; font-weight: 600;">
                                            <th>Total</th>
                                            <td>
                                                <strong>
                                                    <span class="woocommerce-Price-amount amount">
                                                        <span class="woocommerce-Price-currencySymbol">$</span><span id="checkout-total">0.00</span>
                                                    </span>
                                                </strong>
                                            </td>
                                        </tr>
                                        
                                        <tr>
                                            <td colspan="2">
                                                <div id="payment" class="woocommerce-checkout-payment">
                                                    <div class="form-row place-order mt-20 text-center mt-3">
                                                        <button type="submit" id="place_order_btn" class="xb-btn" disabled style="width: 100%; padding: 15px;">Place Order</button>
                                                        <div id="cart_empty_message" class="text-danger" style="display: none; margin-top: 10px;">
                                                            Your cart is empty. Please add items to your cart before placing an order.
                                                        </div>
                                                        <div id="success_message" class="text-success" style="display: none; margin-top: 10px;">
                                                            Your order has been placed successfully!
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

</main>

@include('partials/footer')

<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>

<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    function fetchCart() {
        $.ajax({
            url: '{{ route('api.cart.get') }}',
            method: 'GET',
            success: function(response) {
                $('.header-mini-cart').empty();
                let total = 0;
                let itemCount = 0;

                if (response.items.length === 0) {
                    $('.header-mini-cart').append('<p>Your cart is empty.</p>');
                } else {
                    response.items.forEach(item => {
                        const discount = item.product.discount || 0;
                        const price = item.product.price;
                        const discountedPrice = discount ? price * (1 - (discount / 100)) : price;
                        const itemTotal = discountedPrice * item.quantity;
                        itemCount += item.quantity;

                        $('.header-mini-cart').append(`
                            <div class="woocommerce-mini-cart-item d-flex align-items-center" style="padding: 10px;">
                                <div class="mini-cart-img" style="margin-right: 10px;">
                                    <img src="${item.product_image || 'path/to/default/image.jpg'}" alt="${item.product.name}" style="width: 50px; height: 50px; object-fit: cover;">
                                </div>
                                <div class="mini-cart-content" style="flex-grow: 1;">
                                    <h4 class="product-title" style="margin: 0; font-size: 14px;">
                                        <a href="#" style="text-decoration: none; color: #000;">${item.product.name}</a>
                                    </h4>
                                    <div class="mini-cart-price" style="margin-top: 5px;">
                                        ${item.quantity} × <span style="color: red;">$${discountedPrice.toFixed(2)}</span>
                                    </div>
                                </div>
                                <div class="remove-button" style="margin-left: auto;">
                                    <a href="#" class="remove remove_from_cart_button" data-product_id="${item.product.id}" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: lightgrey; text-align: center; line-height: 20px; color: red; font-size: 14px;">×</a>
                                </div>
                            </div>
                        `);
                        total += itemTotal;
                    });

                    $('.header-mini-cart').append(`
                        <p class="woocommerce-mini-cart__total">
                            <strong>Subtotal:</strong>
                            <span class="woocommerce-Price-amount">$${total.toFixed(2)}</span>
                        </p>
                        <p class="checkout-link">
                            <a href="/viewCart" class="button wc-forward">View cart</a>
                            <a href="/Checkout" class="button checkout wc-forward">Checkout</a>
                        </p>
                    `);
                    $('.mini-cart-count').text(itemCount);
                }
            }
        });
    }

    function fetchCartForCheckout() {
        $.ajax({
            url: '{{ route('api.cart.get') }}',
            method: 'GET',
            success: function(response) {
                $('#order_review tbody').empty();
                let subtotal = 0;

                if (response.items.length === 0) {
                    $('#order_review tbody').append('<tr><td colspan="2" style="text-align: center; padding: 30px;">Your cart is empty.</td></tr>');
                    $('#place_order_btn').prop('disabled', true);
                    $('#cart_empty_message').show();
                } else {
                    $('#place_order_btn').prop('disabled', false);
                    $('#cart_empty_message').hide();
                    
                    response.items.forEach(item => {
                        const discount = item.product.discount;
                        const price = item.product.price;
                        const discountedPrice = discount ? price * (1 - (discount / 100)) : price;
                        const itemTotal = discountedPrice * item.quantity;

                        $('#order_review tbody').append(`
                            <tr class="cart_single">
                                <td class="product-name">
                                    ${item.product.name} <br/>
                                    <small>Options: ${item.formatted_options}</small>
                                    <strong class="product-quantity">&times; ${item.quantity}</strong>
                                </td>
                                <td class="product-total">
                                    <span class="woocommerce-Price-amount amount">
                                        <span class="woocommerce-Price-currencySymbol">$</span>${itemTotal.toFixed(2)}
                                    </span>
                                </td>
                            </tr>
                        `);
                        subtotal += itemTotal;
                    });

                    // Set the subtotal
                    $('#checkout-subtotal').text(subtotal.toFixed(2));
                    
                    // Check if there's an applied coupon to calculate the final total
                    checkAppliedCouponAndUpdateTotals(subtotal);
                }
            }
        });
    }

    // Remove item from mini cart
    $(document).on('click', '.remove', function(event) {
        event.preventDefault();
        const productId = $(this).data('product_id');

        $.ajax({
            url: '{{ route('cart.remove') }}',
            method: 'POST',
            data: {
                product_id: productId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    fetchCart();
                    fetchCartForCheckout();
                }
            }
        });
    });

    // Coupon functionality
    let appliedCoupon = null;

    function displayMessage(message, type = 'success') {
        const messageContainer = $('#coupon-message');
        messageContainer.text(message).removeClass('success error').addClass(type).show();
        setTimeout(() => {
            messageContainer.fadeOut();
        }, 5000);
    }

    $('#apply-coupon').click(function() {
        const couponCode = $('#coupon-code').val().trim();
        
        if (!couponCode) {
            displayMessage('Please enter a coupon code.', 'error');
            return;
        }

        $(this).prop('disabled', true).text('Applying...');

        $.ajax({
            url: '{{ route("coupon.apply") }}',
            method: 'POST',
            data: {
                coupon_code: couponCode,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    appliedCoupon = response.coupon;
                    displayCouponApplied(response);
                    displayMessage(response.message, 'success');
                    $('#coupon-code').val('').prop('disabled', true);
                    $('#apply-coupon').hide();
                    $('#remove-coupon').show();
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                displayMessage(response.message || 'Failed to apply coupon.', 'error');
            },
            complete: function() {
                $('#apply-coupon').prop('disabled', false).text('Apply');
            }
        });
    });

    $('#remove-coupon').click(function() {
        $.ajax({
            url: '{{ route("coupon.remove") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    appliedCoupon = null;
                    hideCouponApplied();
                    displayMessage(response.message, 'success');
                    $('#coupon-code').val('').prop('disabled', false);
                    $('#apply-coupon').show();
                    $('#remove-coupon').hide();
                    
                    // Reset total to subtotal when coupon is removed
                    const subtotal = parseFloat($('#checkout-subtotal').text());
                    updateCheckoutTotal(subtotal);
                }
            }
        });
    });

    function displayCouponApplied(response) {
        $('#coupon-name').text(response.coupon.name);
        $('#coupon-discount').text(response.coupon.discount_percentage + '%');
        $('#discount-amount').text(response.coupon.discount_amount.toFixed(2));
        $('#applied-coupon').show();
        $('.discount-row').show();
        updateCheckoutTotal(response.final_total);
    }

    function hideCouponApplied() {
        $('#applied-coupon').hide();
        $('.discount-row').hide();
        $('#discount-amount').text('0.00');
    }

    function updateCheckoutTotal(finalTotal) {
        $('#checkout-total').text(finalTotal.toFixed(2));
    }

    // FIXED FUNCTION - This was the main issue
    function checkAppliedCouponAndUpdateTotals(subtotal) {
        $.ajax({
            url: '{{ route("coupon.applied") }}',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    appliedCoupon = response.coupon;
                    
                    // Calculate discount amount based on current subtotal
                    const discountAmount = (subtotal * response.coupon.discount_percentage) / 100;
                    const finalTotal = subtotal - discountAmount;
                    
                    // Update the display with calculated values
                    $('#coupon-name').text(response.coupon.name);
                    $('#coupon-discount').text(response.coupon.discount_percentage + '%');
                    $('#discount-amount').text(discountAmount.toFixed(2));
                    $('#applied-coupon').show();
                    $('.discount-row').show();
                    
                    // Update the total with the discount applied
                    updateCheckoutTotal(finalTotal);
                    
                    // Update the form elements
                    $('#coupon-code').val(response.coupon.code).prop('disabled', true);
                    $('#apply-coupon').hide();
                    $('#remove-coupon').show();
                } else {
                    // No coupon applied, set total to subtotal
                    updateCheckoutTotal(subtotal);
                }
            },
            error: function() {
                // If there's an error, just set total to subtotal
                updateCheckoutTotal(subtotal);
            }
        });
    }

    // Separate function for initial page load check (legacy support)
    function checkAppliedCoupon() {
        // This function is now handled by checkAppliedCouponAndUpdateTotals
        // Keep it for compatibility but make it do nothing
        return;
    }

    // Form submission
    $('#checkout_form').on('submit', function(e) {
        e.preventDefault();
        let formData = $(this).serialize();

        $.ajax({
            url: '{{ route('place.order') }}',
            method: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#place_order_btn').hide();
                    $('#success_message').show();
                    $('.mini-cart-count').text('0');
                    $('.header-mini-cart').empty();
                    
                    if (response.billing_email) {
                        $('#success_message').append('<p>Check your email for your receipt! 😊</p>');
                    }
                }
            },
            error: function(xhr) {
                $('.error-message').empty();

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        let errorField = $('#' + key + '_error');
                        if (errorField.length) {
                            errorField.text(value);
                        }
                    });
                } else {
                    alert('Failed to place the order. Please try again.');
                }
            }
        });
    });

    // Initialize
    $(document).ready(function() {
        $('.header-shop-cart a').on('click', function() {
            $('.header-mini-cart').toggle();
        });
        
        fetchCart();
        fetchCartForCheckout(); // This now handles coupon checking and total calculation
    });
</script>
</body>
</html>