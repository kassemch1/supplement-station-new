<!doctype html>
<html lang="zxx">


<head>

    <!--========= Required meta tags =========-->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">


    <title>Checkout</title>

    <link rel="shortcut icon" href="assets/img/favicon.png" type="images/x-icon"/>

    <!-- css include -->
    <link rel="stylesheet" href={{asset("assets/css/bootstrap.min.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/fontawesome.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/animate.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/swiper.min.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/jquery-ui.min.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/magnific-popup.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/main.css")}}>
</head>

<body>

<!-- backtotop - start -->
<div class="xb-backtotop">
    <a href="#" class="scroll">
        <i class="far fa-arrow-up"></i>
    </a>
</div>
<!-- backtotop - end -->
@include('partials/navBar',['categories'=>$categories])

    <div class="body-overlay"></div>

    <!-- main area start  -->
    <main>
        <!-- breadcrumb start -->
        <section class="breadcrumb position-bottom bg_img" data-background="assets/img/bg/page_title2.jpg">
            <div class="container">
                <div class="breadcrumb__content text-center">
                    <h2 class="breadcrumb__title">Checkout</h2>
                    <ul class="breadcrumb__list clearfix">
                        <li class="breadcrumb-item"><a href="index.html">Supplement Station</a></li>
                        <li class="breadcrumb-item">Checkout</li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- breadcrumb end -->

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
                                                <span class="error-message" id="billing_first_name_error" style="color: red; font-weight: bold" ></span>
                                            </p>

                                            <p class="form-row">
                                                <label for="billing_phone">Phone <abbr class="required" title="required">*</abbr></label>
                                                <input type="tel" name="billing_phone" id="billing_phone" placeholder="Phone Number" required style="border: 1px solid #ccc; padding: 0.5rem; width: 100%; border-radius: 4px;" />
                                            <span class="error-message" id="billing_phone_error" style="color: red; font-weight: bold" ></span>
{{--                                                @error('billing_phone')--}}
{{--                                                <span class="text-danger">{{ $message }}</span>--}}
{{--                                                @enderror--}}
                                            </p>

                                            <p class="form-row">
                                                <label for="billing_email">Email (<abbr class="required" title="required">optional</abbr>)</label>
                                                <input type="email" name="billing_email" id="billing_email" placeholder="Email"  style="border: 1px solid #ccc; padding: 0.5rem; width: 100%; border-radius: 4px;" />
                                                <small class="form-text text-muted " style="color: red !important;">Note:Providing your email is optional, but it's best to enter it to receive a receipt.</small>
                                                <span class="error-message" id="billing_email_error" style="color: red; font-weight: bold" ></span>
                                            </p>

                                            <p class="form-row">
                                                <label for="billing_address_1">Address <abbr class="required" title="required">*</abbr></label>
                                                <input type="text" name="billing_address_1" id="billing_address_1" placeholder="Street address" required style="border: 1px solid #ccc; padding: 0.5rem; width: 100%; border-radius: 4px;" />
                                                <span class="error-message" id="billing_address_1_error" style="color: red; font-weight: bold" ></span>
                                            </p>

                                            <p class="form-row">
                                                <label for="billing_city">Town / City <abbr class="required" title="required">*</abbr></label>
                                                <input type="text" name="billing_city" id="billing_city" placeholder="City" required style="border: 1px solid #ccc; padding: 0.5rem; width: 100%; border-radius: 4px;" />
                                                <span class="error-message" id="billing_city_error" style="color: red; font-weight: bold" ></span>
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
                                                        <span class="woocommerce-Price-currencySymbol">$</span>0.00
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr class="order-total">
                                                <th>Total</th>
                                                <td>
                                                    <strong>
                                                        <span class="woocommerce-Price-amount amount">
                                                            <span class="woocommerce-Price-currencySymbol">$</span>0.00
                                                        </span>
                                                    </strong>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <div id="payment" class="woocommerce-checkout-payment">
                                                        <div class="form-row place-order mt-20 text-center mt-3">
                                                            <button type="submit" id="place_order_btn" class="xb-btn" disabled>Place Order</button>
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
        <!-- end checkout-section -->


    </main>
    <!-- main area end  -->

    <!-- footer strt -->
        @include('partials/footer')
    <!-- footer end -->


</div>

<script src={{asset("assets/js/jquery-3.7.1.min.js")}}></script>
<script src={{asset("assets/js/bootstrap.bundle.min.js")}}></script>
<script src={{asset("assets/js/swiper.min.js")}}></script>
<script src="{{asset("assets/js/wow.min.js")}}"></script>
<script src={{asset("assets/js/jquery.magnific-popup.min.js")}}></script>
<script src={{asset("assets/js/touchspin.js")}}></script>
<script src={{asset("assets/js/jquery-ui.min.js")}}></script>
<script src={{asset("assets/js/jquery.inview.min.js")}}></script>
<script src={{asset("assets/js/jquery.easing.js")}}></script>
<script src={{asset("assets/js/scrollspy.js")}}></script>
<script src={{asset("assets/js/main.js")}}></script>

<script>
    function fetchCart() {
        $.ajax({
            url: '{{ route('api.cart.get') }}',
            method: 'GET',
            success: function(response) {
                console.log("response", response.items); // Check response structure

                if (!response.items || !Array.isArray(response.items)) {
                    console.error("Invalid response structure");
                    return;
                }

                $('.header-mini-cart').empty(); // Clear previous items
                let total = 0;
                let itemCount = 0; // Initialize item count

                if (response.items.length === 0) {
                    $('.header-mini-cart').append('<p>Your cart is empty.</p>');
                } else {
                    response.items.forEach(item => {
                        if (!item.product || !item.product.price || !item.product.name) {
                            console.error("Invalid item structure");
                            return;
                        }

                        const discount = item.product.discount || 0; // Get discount percentage
                        const price = item.product.price;
                        const discountedPrice = discount ? price * (1 - (discount / 100)) : price; // Apply discount
                        const itemTotal = discountedPrice * item.quantity; // Calculate total for this item
                        itemCount += item.quantity; // Sum the quantities for the cart count

                        $('.header-mini-cart').append(`
                            <div class="woocommerce-mini-cart-item d-flex align-items-center" style="padding: 10px;">
                                <div class="mini-cart-img" style="margin-right: 10px;">
                                    <img src="${item.product_image || 'path/to/default/image.jpg'}" alt="${item.product.name}" style="width: 50px; height: 50px; object-fit: cover;">
                                </div>
                                <div class="mini-cart-content" style="flex-grow: 1;">
                                    <h4 class="product-title" style="margin: 0; font-size: 14px;">
                                        <a href="shop-details.html" style="text-decoration: none; color: #000;">${item.product.name}</a>
                                    </h4>
                                    <div class="mini-cart-price" style="margin-top: 5px;">
                                        ${item.quantity} ×
                                        <span class="woocommerce-Price-amount amount" style="color: red;">$${discountedPrice.toFixed(2)}</span>

                                    </div>
                                </div>
                                <div class="remove-button" style="margin-left: auto;">
                                    <a href="#" class="remove remove_from_cart_button" data-product_id="${item.product.id}" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: lightgrey; text-align: center; line-height: 20px; color: red; font-size: 14px;">×</a>
                                </div>
                            </div>
                        `);

                        total += itemTotal; // Sum the total amount
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
                    $('.mini-cart-count').text(itemCount); // Update the cart count in the header
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    // Fetch cart items on page load
    $(document).ready(function() {
        $('.header-shop-cart a').on('click', function() {
            $('.header-mini-cart').toggle(); // Toggle visibility on click
        });
        fetchCart(); // Fetch cart items
    });

   // Remove item from mini cart
$(document).on('click', '.remove', function(event) {
    event.preventDefault(); // Prevent the default link behavior

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
                alert(response.message);
                fetchCart(); // Refresh the cart items
            } else {
                alert('Failed to remove item from cart.'); // Handle unexpected response
            }
        },
        error: function(xhr, status, error) {
            // Check if the response contains a JSON message
            try {
                const errResponse = JSON.parse(xhr.responseText);
                alert(errResponse.message || 'Failed to remove item from cart.');
            } catch (e) {
                alert('Failed to remove item from cart.');
            }
        }
    });
});
</script>






<script>
$(document).ready(function() {
    function fetchCartForCheckout() {
        $.ajax({
            url: '{{ route('api.cart.get') }}',
            method: 'GET',
            success: function(response) {
                $('#order_review tbody').empty();
                let subtotal = 0;

                if (response.items.length === 0) {
                    $('#order_review tbody').append('<tr><td colspan="2">Your cart is empty.</td></tr>');
                    $('#place_order_btn').prop('disabled', true); // Disable button if cart is empty
                    $('#cart_empty_message').show(); // Show message if cart is empty
                } else {
                    $('#place_order_btn').prop('disabled', false); // Enable button if cart has items
                    $('#cart_empty_message').hide(); // Hide message if cart has items
                    response.items.forEach(item => {

                        const discount = item.product.discount;
                        const price = item.product.price;
                        const discountedPrice = discount ? price * (1 - (discount / 100)) : price; // Apply discount
                        const itemTotal = discountedPrice * item.quantity; // Calculate total for this item

                        $('#order_review tbody').append(
                            `<tr class="cart_single">
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
                            </tr>`
                        );
                        subtotal += itemTotal; // Update subtotal with item total
                    });

                    $('#order_review .cart-subtotal td').html(
                        `<span class="woocommerce-Price-amount amount">
                            <span class="woocommerce-Price-currencySymbol">$</span>${subtotal.toFixed(2)}
                        </span>`
                    );

                    $('#order_review .order-total td strong').html(
                        `<span class="woocommerce-Price-amount amount">
                            <span class="woocommerce-Price-currencySymbol">$</span>${subtotal.toFixed(2)}
                        </span>`
                    );
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    fetchCartForCheckout();

});
$(document).ready(function() {
    $('#checkout_form').on('submit', function(e) {
        e.preventDefault();

        // Collect form data
        let formData = $(this).serialize();

        $.ajax({
            url: '{{ route('place.order') }}',
            method: 'POST',
            data: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    $('#place_order_btn').hide();
                    $('#success_message').show();
                    // Reset mini cart items count to 0
                    $('.mini-cart-count').text('0'); // Reset mini cart count to 0

                    // Optionally clear the mini cart content
                    $('.header-mini-cart').empty();
                    if (response.billing_email) {
                        $('#success_message').append('<p>Check your email for your invoice &#128516;.</p>');
                    }
                } else {
                    alert('Order could not be placed.');
                }
            },
            error: function(xhr) {
                // Clear previous error messages
                $('.error-message').empty();

                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;

                    // Display error messages under the corresponding fields
                    $.each(errors, function(key, value) {
                        let errorField = $('#' + key + '_error');
                        if (errorField.length) {
                            errorField.text(value);
                        } else {
                            // For fields without specific error containers, you can use a general error display
                            alert('Validation errors:\n' + value);
                        }
                    });
                } else {
                    alert('Failed to place the order. Please try again.');
                }
            }

            {{--//keep this way to use it in @error--}}


            // error: function(xhr) {
            //     // Clear previous error messages
            //     $('.text-danger').remove();
            //
            //     if (xhr.status === 422) {
            //         let errors = xhr.responseJSON.errors;
            //
            //         // Display error messages under the corresponding fields
            //         $.each(errors, function(key, value) {
            //             let field = $('#' + key);
            //             if (field.length) {
            //                 field.after('<span class="text-danger">' + value + '</span>');
            //             }
            //         });
            //     } else {
            //         alert('Failed to place the order. Please try again.');
            //     }
            // }
        });
    });
});

</script>


</body>


</html>
