<!doctype html>
<html lang="zxx">


<head>

    <!--========= Required meta tags =========-->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Purefit - Health Supplement Landing Page</title>

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
@include('partials/navBar')

    <div class="body-overlay"></div>

    <!-- main area start  -->
    <main>
        <!-- breadcrumb start -->
        <section class="breadcrumb position-bottom bg_img" data-background="assets/img/bg/page_title.png">
            <div class="container">
                <div class="breadcrumb__content text-center">
                    <h2 class="breadcrumb__title">Checkout</h2>
                    <ul class="breadcrumb__list clearfix">
                        <li class="breadcrumb-item"><a href="index.html">Purefit</a></li>
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
                            
                            <form name="checkout" method="post" class="checkout woocommerce-checkout" action="{{ route('place.order') }}" enctype="multipart/form-data">
                                @csrf
                            
                                <div class="col2-set" id="customer_details">
                                    <div class="coll-1">
                                        <div class="woocommerce-billing-fields">
                                            <h3>Billing Details</h3>
                            
                                            <p class="form-row">
                                                <label for="billing_first_name">Name <abbr class="required" title="required">*</abbr></label>
                                                <input type="text" name="billing_first_name" id="billing_first_name" placeholder="Name" required style="border: 1px solid #ccc; padding: 0.5rem; width: 100%; border-radius: 4px;" />
                                            </p>
                                            
                                            <p class="form-row">
                                                <label for="billing_phone">Phone <abbr class="required" title="required">*</abbr></label>
                                                <input type="tel" name="billing_phone" id="billing_phone" placeholder="Phone Number" required style="border: 1px solid #ccc; padding: 0.5rem; width: 100%; border-radius: 4px;" />
                                            </p>
                                            
                                            <p class="form-row">
                                                <label for="billing_address_1">Address <abbr class="required" title="required">*</abbr></label>
                                                <input type="text" name="billing_address_1" id="billing_address_1" placeholder="Street address" required style="border: 1px solid #ccc; padding: 0.5rem; width: 100%; border-radius: 4px;" />
                                            </p>
                                            
                                            <p class="form-row">
                                                <label for="billing_city">Town / City <abbr class="required" title="required">*</abbr></label>
                                                <input type="text" name="billing_city" id="billing_city" placeholder="City" required style="border: 1px solid #ccc; padding: 0.5rem; width: 100%; border-radius: 4px;" />
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
    <footer class="position-top bg_img pb-70" data-background="assets/img/bg/footer_bg.png">
        <div class="container">
            <div class="contact pb-100">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="xb-contact contact-mt--255">
                            <div class="contact-title mb-35">
                                <span><img src="assets/img/icon/directbox-notif.svg" alt="">Contact Us</span>
                                <h3>Do you have questions or went more <br> information?</h3>
                            </div>
                            <form class="contact-from" action="#!">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="xb-item--field">
                                            <span><img src="assets/img/icon/c_user.svg" alt=""></span>
                                            <input type="text" placeholder="Steven Kevin">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="xb-item--field">
                                            <span><img src="assets/img/icon/c_mail.svg" alt=""></span>
                                            <input type="text" placeholder="purefit@gmail.com">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="xb-item--field">
                                            <span><img src="assets/img/icon/c_call.svg" alt=""></span>
                                            <input type="text" placeholder="+91 081 0256 023">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="xb-item--field">
                                            <span><img src="assets/img/icon/c_message.svg" alt=""></span>
                                            <textarea name="message" id="message" cols="30" rows="10"
                                                      placeholder="Write Your Message..."></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button class="thm-btn thm-btn--black" type="submit">Send Message</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="contact-info contact-mt--255 mt-md-30">
                            <div class="xb-item--head">
                                <div class="xb-item--address">
                                    <h3><img src="assets/img/icon/location.svg" alt="">our address</h3>
                                    <p>100 Orchard st, New <br> York,NY 100025 USA</p>
                                </div>
                                <div class="xb-item--open">
                                    <p>Monday - Friday <br>
                                        08:00AM - 05:00PM</p>
                                    <a href="mailto:purefit@gmail.com"><img src="assets/img/icon/sms-tracking.svg" alt="">purefit@gmail.com</a>
                                </div>
                                <ul class="xb-item--social ul_li mt-30">
                                    <li><a href="#!"><i class="fab fa-telegram-plane"></i></a></li>
                                    <li><a href="#!"><i class="fab fa-whatsapp"></i></a></li>
                                    <li><a href="#!"><i class="fab fa-facebook-f"></i></a></li>
                                </ul>
                            </div>
                            <div class="xb-item--cta" data-background="assets/img/bg/cta_bg.jpg">
                                <p>Our help desk is a vailable for you <br> every day, 07:00AM - 10:00PM</p>
                                <h3>+91 081 256 023</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-inner">
                <div class="footer-logo mb-25 text-center">
                    <img src="assets/img/logo/f_logo.png" alt="">
                </div>
                <div class="sec-title sec-title--white text-center mb-50">
                    <h2 class="title">in a healthy body, healthy mind</h2>
                </div>
                <ul class="footer-nav ul_li_center">
                    <li><a href="#!">all products</a></li>
                    <li><a href="#!">track order</a></li>
                    <li><a href="#!">my account</a></li>
                    <li><a href="#!">gift cards</a></li>
                    <li><a href="#!">our story</a></li>
                    <li><a href="#!">careers</a></li>
                    <li><a href="#!">contact</a></li>
                </ul>
                <div class="footer-bottom mt-50 ul_li_between">
                    <div class="footer-copyright mt-30">
                        Copyright © 2024 purefit All rights reserved.
                    </div>
                    <ul class="footer-links ul_li mt-30">
                        <li><a href="#!">terms of conditions</a></li>
                        <li><a href="#!">privacy pllicy</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
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

<!-- jquery include -->
{{--<script src="assets/js/jquery-3.7.1.min.js"></script>--}}
{{--<script src="assets/js/bootstrap.bundle.min.js"></script>--}}
{{--<script src="assets/js/swiper.min.js"></script>--}}
{{--<script src="assets/js/wow.min.js"></script>--}}
{{--<script src="assets/js/jquery.magnific-popup.min.js"></script>--}}
{{--<script src="assets/js/touchspin.js"></script>--}}
{{--<script src="assets/js/jquery-ui.min.js"></script>--}}
{{--<script src="assets/js/jquery.inview.min.js"></script>--}}
{{--<script src="assets/js/jquery.easing.js"></script>--}}
{{--<script src="assets/js/scrollspy.js"></script>--}}
{{--<script src="assets/js/main.js"></script>--}}
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
                        const itemTotal = item.product.price * item.quantity;
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
                        subtotal += itemTotal;
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

</script>



</body>


</html>
