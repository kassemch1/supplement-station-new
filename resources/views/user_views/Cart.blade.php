<!doctype html>
<html lang="zxx">

<head>

    <!--========= Required meta tags =========-->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Cart</title>

    <link rel="shortcut icon" href={{ asset('assets/img/logo/preloader2.png')}} type="images/x-icon"/>

    <!-- css include -->
    <link rel="stylesheet" href={{asset("assets/css/bootstrap.min.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/fontawesome.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/animate.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/swiper.min.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/jquery-ui.min.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/magnific-popup.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/main.css")}}>

    <style>
        .quantity {
            display: flex;
            align-items: center;
        }

        .quantity button {
            background-color: transparent; /* Light grey background */
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #333;
            cursor: pointer;
            font-size: 16px;
            height: 40px;
            line-height: 1;
            padding: 10px;
            width: 40px;
            text-align: center;
            transition: background-color 0.3s, border-color 0.3s; /* Smooth transition */
        }

        .quantity input {
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            height: 40px;
            text-align: center;
            width: 60px;
        }

        .quantity button:focus,
        .quantity input:focus {
            outline: none;
        }

        .quantity button.quantity-minus {
            border-radius: 4px 0 0 4px;
        }

        .quantity button.quantity-plus {
            border-radius: 0 4px 4px 0;
        }
        @media (max-width: 991px) {
            .quantity button.quantity-plus {
                background-color: transparent; /* Light grey background */
                border: 1px solid #ddd;
                border-radius: 4px;
                color: #333;
                cursor: pointer;
                font-size: 16px;
                height: 40px;
                line-height: 1;
                padding: 10px;
                width: 30px;
                text-align: center;
                transition: background-color 0.3s, border-color 0.3s; /* Smooth transition */
            }
            .quantity input {
                border: 1px solid #ddd;
                border-radius: 4px;
                font-size: 16px;
                height: 40px;
                text-align: center;
                width: 50px;
            }
            .quantity {
                display: flex;
                align-items: center;
                position: relative;
                left: -15px;
        }
            .quantity button:hover {
                background-color:#A02334;
                border-color: orange;
                color: white; /* Optional: change text color to white for better contrast */
            }

        }

        .quantity input,
        .quantity button {
            flex: 1;
        }

        .quantity input {
            border-left: 0;
            border-right: 0;
        }

        /* Hover effect */
        .quantity button:hover {
            background-color:#A02334;
            border-color: #A02334;
            color: white; /* Optional: change text color to white for better contrast */
        }

        #message-container {
            display: none; /* Initially hidden */
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            color: #fff; /* White text color */
            font-size: 16px;
        }

        #message-container.success {
            background-color: #4caf50; /* Green background */
            border: 1px solid #388e3c; /* Darker green border */
        }

        #message-container.error {
            background-color: #f44336; /* Red background */
            border: 1px solid #c62828; /* Darker red border */
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
<!-- backtotop - end -->

@include('partials/navBar',['categories'=>$categories])
    <!-- header end -->


    <div class="body-overlay"></div>

    <!-- main area start  -->
    <main>
        <!-- breadcrumb start -->
        <section class="breadcrumb position-bottom bg_img" data-background="{{ asset('assets/img/bg/shop-cart-banner2.png')}}">
            <div class="container">
                <div class="breadcrumb__content text-center">
                    <h2 class="breadcrumb__title">Cart</h2>
                    <ul class="breadcrumb__list clearfix">
                        <li class="breadcrumb-item"><a href="index.html">Supplement Station</a></li>
                        <li class="breadcrumb-item">Cart</li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- breadcrumb end -->

        <!-- start cart-section -->
        <section class="cart-section woocommerce-cart pt-115 pb-385">
            <div class="container">
                <div id="message-container" style="display: none;"></div>
                <div class="row">
                    <div class="col col-xs-12">
                        <div class="woocommerce">
                            <form method="post">
                                <table class="shop_table shop_table_responsive cart">
                                    <thead>
                                    <tr>
                                        <th class="product-remove">&nbsp;</th>
                                        <th class="product-thumbnail">&nbsp;</th>
                                        <th class="product-name">Product</th>
                                        <th class="product-price">Price</th>
                                        <th class="product-options">Options</th>
                                        <th class="product-quantity">Quantity</th>
                                        <th class="product-subtotal">Total</th>
                                    </tr>
                                    </thead>
                                    <tbody id="cart-items">


                                    </tbody>
{{--                                    <tr>--}}
{{--                                        <td colspan="6" class="actions">--}}

{{--                                            <button class="xb-btn" type="button">Update Cart--}}
{{--                                            </button>--}}


{{--                                        </td>--}}
{{--                                    </tr>--}}
                                </table>
                            </form>

                            <div class="cart-collaterals">
                                <div class="cart_totals calculated_shipping">
                                    <h2>Cart Totals</h2>
                                    <table class="shop_table shop_table_responsive">
                                        <tr class="cart-subtotal">
                                            <th>Subtotal</th>
                                            <td data-title="Subtotal">$<strong>
                                                <span class="woocommerce-Price-amount amount" id="cart-subtotal">
                                                    <span
                                                        class="woocommerce-Price-currencySymbol"></span>0</span></strong>
                                            </td>
                                        </tr>
                                        <tr class="shipping">
                                            <th>Shipping</th>
                                            <td data-title="Shipping">
                                                Free Shipping
                                                <input type="hidden" name="shipping_method[0]" data-index="0"
                                                       id="shipping_method_0" value="free_shipping:1"
                                                       class="shipping_method" />
                                                <form class="woocommerce-shipping-calculator"
                                                      action="http://localhost/wp/?page_id=5" method="post">
                                                    <p><a href="#!" class="shipping-calculator-button">Calculate
                                                            Shipping</a></p>
                                                    <section class="shipping-calculator-form" style="display:none;">
                                                        <h2 class="hidden">Cart total</h2>
                                                        <p class="form-row form-row-wide"
                                                           id="calc_shipping_country_field">
                                                            <select name="calc_shipping_country"
                                                                    id="calc_shipping_country" class="country_to_state"
                                                                    rel="calc_shipping_state">
                                                                <option value="">Select a country&hellip;</option>
                                                                <option value="AX" selected='selected'>&#197;land
                                                                    Islands</option>

                                                            </select>
                                                        </p>
                                                        <p class="form-row form-row-wide"
                                                           id="calc_shipping_state_field">
                                                            <input type="hidden" name="calc_shipping_state"
                                                                   id="calc_shipping_state" />
                                                        </p>
                                                        <p class="form-row form-row-wide"
                                                           id="calc_shipping_postcode_field">
                                                            <input type="text" class="input-text" value=""
                                                                   placeholder="Postcode / ZIP"
                                                                   name="calc_shipping_postcode"
                                                                   id="calc_shipping_postcode" />
                                                        </p>
                                                        <p>
                                                            <button type="submit" name="calc_shipping" value="1"
                                                                    class="button">Update Totals</button>
                                                        </p>
                                                        <input type="hidden" id="_wpnonced" name="_wpnonce"
                                                               value="918724a9c2" />
                                                        <input type="hidden" name="_wp_http_referer"
                                                               value="/wp/?page_id=5" />
                                                    </section>
                                                </form>
                                            </td>
                                        </tr>

                                        <tr class="cart-total">
                                            <th>Total</th>
                                            <td data-title="Total">$<strong><span
                                                        class="woocommerce-Price-amount amount" id="cart-total"><span
                                                            class="woocommerce-Price-currencySymbol"></span>0</span></strong>
                                            </td>
                                        </tr>
                                    </table>

                                    <div class="wc-proceed-to-checkout">
                                        <a  class="checkout-button xb-btn" href="{{ route('checkout')}} ">Proceed to Checkout </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- end cart-section -->

    </main>
    <!-- main area end  -->

    <!-- footer strt -->
        @include('partials/footer')
    <!-- footer end -->




<!-- jquery include -->
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {

        function displayMessage(message, type = 'success') {
            const messageContainer = $('#message-container');
            messageContainer.text(message).removeClass('success error').addClass(type).show();
            setTimeout(() => {
                messageContainer.fadeOut();
            }, 3000); // Hide after 3 seconds
        }

        // Function to fetch cart items
        function fetchCart() {
            $.ajax({
            url: '{{ route('api.cart.get') }}',
            method: 'GET',
            success: function(response) {
    $('#cart-items').empty(); // Clear previous items
    let total = 0;

    if (response.items.length === 0) {
        $('#cart-items').append('<tr><td colspan="6">Your cart is empty.</td></tr>');
    } else {
        response.items.forEach(item => {
            const itemTotal = item.subtotal; // Use subtotal with discount
            $('#cart-items').append(`
                <tr class="cart_single">
                    <td class="product-remove">
                        <a href="#!" class="remove" title="Remove this item"
                           data-product_id="${item.product.id}">&times;</a>
                    </td>
                    <td class="product-thumbnail">
                        <a href="#!">
                            <img width="57" height="70" src="${item.product_image}"
                                 class="attachment-shop_thumbnail size-shop_thumbnail wp-post-image"
                                 alt="${item.product.name}" />
                        </a>
                    </td>
                    <td class="product-name" data-title="Product">
                        <a href="#!">${item.product.name}</a>
                    </td>
                    <td class="product-price" data-title="Price">
                        <span class="woocommerce-Price-amount amount">
                            <span class="woocommerce-Price-currencySymbol">$</span>${item.discounted_price.toFixed(2)} <!-- Display discounted price -->
                        </span>
                    </td>
                    <td class="product-options" data-title="Options">
                        <input type="hidden" class="selected-options" value='${item.selected_options}' />
                        <span>${item.formatted_options}</span>
                    </td>
                    <td class="product-quantity" data-title="Quantity">
                        <div class="quantity">
                            <button type="button" class="quantity-minus" data-product_id="${item.product.id}">-</button>
                            <input type="number" step="1" min="1" max="9999" value="${item.quantity}"
                             title="Qty"
                             class="product-count input-text qty text product-count form-control"
                             data-product_id="${item.product.id}" />
                            <button type="button" class="quantity-plus" data-product_id="${item.product.id}">+</button>
                        </div>
                    </td>
                    <td class="product-subtotal" data-title="Total">
                        <span class="woocommerce-Price-amount amount">
                            <span class="woocommerce-Price-currencySymbol">$</span>${itemTotal.toFixed(2)} <!-- Display subtotal with discount -->
                        </span>
                    </td>
                </tr>
            `);
            total += itemTotal; // Accumulate total for cart
        });

        $('#cart-subtotal').text(total.toFixed(2));
        $('#cart-total').text(total.toFixed(2));
    }
},
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    fetchCart();


        // Update cart item quantity
        $(document).on('change', '.product-count', function() {
            const productId = $(this).data('product_id');
            const quantity = $(this).val();
            const optionsInput = $(this).closest('tr').find('.selected-options'); // Hidden input for options
            const selectedOptions = optionsInput.val(); // Get selected options as JSON string

            console.log(selectedOptions); // Check if selectedOptions are retrieved correctly

            $.ajax({
                url: '{{ route('cart.update') }}', // Ensure this matches your route name in web.php
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity,
                    selected_options: selectedOptions, // Include selected options
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    displayMessage(response.message);
                    fetchCart();
                },
                error: function(xhr, status, error) {
                    displayMessage('Failed to update cart.', 'error');
                }
            });
        });

// Handle plus and minus buttons
        $(document).on('click', '.quantity-plus', function() {
            const input = $(this).siblings('.product-count');
            const currentValue = parseInt(input.val());
            const maxValue = parseInt(input.attr('max'));

            if (currentValue < maxValue) {
                input.val(currentValue + 1).trigger('change');
            }
        });

        $(document).on('click', '.quantity-minus', function() {
            const input = $(this).siblings('.product-count');
            const currentValue = parseInt(input.val());
            const minValue = parseInt(input.attr('min'));

            if (currentValue > minValue) {
                input.val(currentValue - 1).trigger('change');
            }
        });

        // Remove item from cart
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
                    displayMessage(response.message);
                    fetchCart();
                },
                error: function(xhr, status, error) {
                    displayMessage('Failed to update cart.', 'error');
                }
            });
        });
    });
</script>


</body>



</html>





