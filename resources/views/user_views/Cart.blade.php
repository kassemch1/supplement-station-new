<!doctype html>
<html lang="zxx">


<!-- Mirrored from html.xpressbuddy.com/purefit/cart.html by HTTrack Website Copier/3.x [XR&CO'2014], Sat, 27 Jul 2024 09:34:26 GMT -->
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
            background-color:#FF4D24;
            border-color: orange;
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

<!-- preloader start -->
<div id="xb-loadding" class="xb-loadding-container">
    <div class="xb-loader">
        <div class="xb-loadding-inner">
            <img src="assets/img/logo/preloader.png" alt="">
        </div>
    </div>
</div>

<!-- preloader end -->


<div class="body_wrap">

    <!-- header start -->
    <header id="home" class="header-area header-default is-sticky">
        <div class="xb-header stricky">
            <div class="container">
                <div class="header__wrap ul_li_between">
                    <div class="header-logo">
                        <a href="index.html"><img src="assets/img/logo/logo.svg" alt=""></a>
                    </div>
                    <div class="main-menu__wrap ul_li navbar navbar-expand-lg">
                        <nav class="main-menu collapse navbar-collapse">
                            <ul>
                                <li class="menu-item-has-children">
                                    <a href="index.html#home"><span>Home</span></a>
                                    <ul class="submenu">
                                        <li><a href="index.html"><span>Home Style 01</span></a></li>
                                        <li><a href="home-2.html"><span>Home Style 02</span></a></li>
                                    </ul>
                                </li>
                                <li><a href="index.html#features"><span>Features</span></a></li>
                                <li class="menu-item-has-children active">
                                    <a href="index.html#shop"><span>Shop</span></a>
                                    <ul class="submenu">
                                        <li><a href="shop.html"><span>Products</span></a></li>
                                        <li><a href="shop-single.html"><span>Single Product</span></a></li>
                                        <li class="active"><a href="cart.html"><span>Cart</span></a></li>
                                        <li><a href="checkout.html"><span>Checkout</span></a></li>
                                    </ul>
                                </li>
                                <li><a href="index.html#pricing"><span>Pricing</span></a></li>
                                <li class="menu-item-has-children">
                                    <a href="index.html#blog"><span>Blog</span></a>
                                    <ul class="submenu">
                                        <li><a href="blog.html"><span>Blog</span></a></li>
                                        <li><a href="blog-single.html"><span>Blog Details</span></a></li>
                                    </ul>
                                </li>
                                <li><a href="index.html#contact"><span>Contact</span></a></li>
                            </ul>
                        </nav>
                        <div class="xb-header-wrap">
                            <div class="xb-header-menu">
                                <div class="xb-header-menu-scroll">
                                    <div class="xb-menu-close xb-hide-xl xb-close"></div>
                                    <div class="xb-logo-mobile xb-hide-xl">
                                        <a href="index.html" rel="home"><img src="assets/img/logo/logo.svg" alt=""></a></div>
                                    <div class="xb-header-mobile-search xb-hide-xl">
                                        <form role="search" action="#">
                                            <input type="text" placeholder="Search..." name="s" class="search-field">
                                        </form>
                                    </div>
                                    <nav class="xb-header-nav">
                                        <ul class="xb-menu-primary clearfix">
                                            <li class="menu-item menu-item-has-children active">
                                                <a href="index.html#home"><span>Home</span></a>
                                                <ul class="sub-menu">
                                                    <li class="active"><a href="index.html"><span>Home Style 01</span></a></li>
                                                    <li><a href="home-2.html"><span>Home Style 02</span></a></li>
                                                </ul>
                                            </li>
                                            <li class="menu-item"><a href="index.html#features"><span>Features</span></a></li>
                                            <li class="menu-item menu-item-has-children">
                                                <a href="index.html#shop"><span>Shop</span></a>
                                                <ul class="sub-menu">
                                                    <li><a href="shop.html"><span>Products</span></a></li>
                                                    <li><a href="shop-single.html"><span>Single Product</span></a></li>
                                                    <li><a href="cart.html"><span>Cart</span></a></li>
                                                    <li><a href="checkout.html"><span>Checkout</span></a></li>
                                                </ul>
                                            </li>
                                            <li class="menu-item"><a href="index.html#pricing"><span>Pricing</span></a></li>
                                            <li class="menu-item menu-item-has-children">
                                                <a href="index.html#blog"><span>Blog</span></a>
                                                <ul class="sub-menu">
                                                    <li><a href="blog.html"><span>Blog</span></a></li>
                                                    <li><a href="blog-single.html"><span>Blog Details</span></a></li>
                                                </ul>
                                            </li>
                                            <li class="menu-item"><a href="index.html#contact"><span>Contact</span></a></li>
                                        </ul>
                                    </nav>
                                </div>
                            </div>
                            <div class="xb-header-menu-backdrop"></div>
                        </div>
                    </div>
                    <div class="header__right d-none d-lg-block">
                        <div class="ul_li">
                            <div class="header-shop-cart">
                                <a href="javascript:void(0);"><img src="assets/img/icon/bag.svg" alt=""><span class="mini-cart-count">2</span></a>
                                <div class="header-mini-cart">
                                    <ul class="woocommerce-mini-cart cart_list product_list_widget list-wrap">
                                        <li class="woocommerce-mini-cart-item d-flex align-items-center">
                                            <a href="#" class="remove remove_from_cart_button">×</a>
                                            <div class="mini-cart-img">
                                                <img src="assets/img/shop/product_02.png" alt="Product">
                                            </div>
                                            <div class="mini-cart-content">
                                                <h4 class="product-title"><a href="shop-details.html">
                                                        xplode powder</a></h4>
                                                <div class="mini-cart-price">1 ×
                                                    <span class="woocommerce-Price-amount amount">$49</span>
                                                </div>
                                            </div>
                                        </li>
                                        <li class="woocommerce-mini-cart-item d-flex align-items-center">
                                            <a href="#" class="remove remove_from_cart_button">×</a>
                                            <div class="mini-cart-img">
                                                <img src="assets/img/shop/product_03.png" alt="Product">
                                            </div>
                                            <div class="mini-cart-content">
                                                <h4 class="product-title"><a href="shop-details.html">creatine powder</a></h4>
                                                <div class="mini-cart-price">2 ×
                                                    <span class="woocommerce-Price-amount amount">$69</span>
                                                </div>
                                            </div>
                                        </li>
                                    </ul>
                                    <p class="woocommerce-mini-cart__total">
                                        <strong>Subtotal:</strong>
                                        <span class="woocommerce-Price-amount">$149</span>
                                    </p>
                                    <p class="checkout-link">
                                        <a href="shop-details.html" class="button wc-forward">View cart</a>
                                        <a href="shop-details.html" class="button checkout wc-forward">Checkout</a>
                                    </p>
                                </div>
                            </div>
                            <a class="header__bar offcanvas-sidebar-btn" href="javascript:void(0);">
                                <div class="header__bar-icon">
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                    <span></span>
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="header-bar-mobile d-lg-none">
                        <a class="header__bar xb-nav-mobile" href="javascript:void(0);">
                            <div class="header__bar-icon">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!-- header end -->

    <!-- sidebar-info start -->
    <div class="offcanvas-sidebar">
        <div class="sidebar-menu-close">
            <a class="xb-close" href="javascript:void(0);"></a>
        </div>
        <div class="sidebar-top mb-65">
            <div class="sidebar-logo mb-40">
                <a href="index.html">
                    <img src="assets/img/logo/logo_black.svg" alt="logo">
                </a>
            </div>
            <div class="sidebar-content">
                Achieving optimal nutrition is a complex endeavor without the inclusion of supplementary support
            </div>
        </div>

        <div class="sidebar-contact-info mb-65">
            <h4 class="sidebar-heading">Contact Information</h4>
            <ul class="sidebar-info-list list-unstyled">
                <li><span><img src="assets/img/icon/i_star.svg" alt=""></span>Wasington SY, UK, NY 12099</li>
                <li><a href="#!"><span><img src="assets/img/icon/i_star.svg" alt=""></span>+81 800 123 06 78</a></li>
                <li><a href="#!"><span><img src="assets/img/icon/i_star.svg" alt=""></span>contact@purefit.com</a></li>
            </ul>
        </div>
        <div class="xb-content-wrap d-flex">
            <div class="xb-title col-auto">Call us:</div>
            <div class="xb-inf-content-wrap col">
                <div class="xb-item-wrap row">
                    <div class="xb-item col-auto ">
                        <span class="item-content"><a href="tel:02456787535" class="tel">024 5678 7535</a></span>
                    </div>
                    <div class="xb-item col-auto "> <span class="item-content"><a href="mailto:support@gmail.com">contact@purefit.com</a></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="sidebar-socials-wrap mt-30"> <a class="social-item" href="https://facebook.com/" target="_blank">Facebook</a><a class="social-item" href="https://www.behance.net/" target="_blank">Behance</a><a class="social-item" href="#" target="_blank">Telegram</a><a class="social-item" href="https://dribbble.com/" target="_blank">Dribbble</a></div>

    </div>
    <!-- sidebar-info end -->

    <div class="body-overlay"></div>

    <!-- main area start  -->
    <main>
        <!-- breadcrumb start -->
        <section class="breadcrumb position-bottom bg_img" data-background="assets/img/bg/page_title.png">
            <div class="container">
                <div class="breadcrumb__content text-center">
                    <h2 class="breadcrumb__title">Cart</h2>
                    <ul class="breadcrumb__list clearfix">
                        <li class="breadcrumb-item"><a href="index.html">Purefit</a></li>
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
                                            <td data-title="Subtotal">$
                                                <span class="woocommerce-Price-amount amount" id="cart-subtotal">
                                                    <span
                                                        class="woocommerce-Price-currencySymbol"></span>0</span>
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

                                        <tr class="order-total">
                                            <th>Total</th>
                                            <td data-title="Total"><strong><span
                                                        class="woocommerce-Price-amount amount"><span
                                                            class="woocommerce-Price-currencySymbol">$</span>430.00</span></strong>
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

    $(document).ready(function() {

        function calculateSubtotal() {
            let subtotal = 0;
            $('.cart_single').each(function() {
                const quantity = parseInt($(this).find('.product-count').val());
                const price = parseFloat($(this).find('.product-price .woocommerce-Price-amount').text().replace(/[^0-9.-]+/g,""));
                subtotal += quantity * price;
            });
            return subtotal;
        }

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
                            const itemTotal = item.product.price * item.quantity;
                            $('#cart-items').append(`
                                <tr class="cart_single">
                                    <td class="product-remove">
                                        <a href="#!" class="remove" title="Remove this item"
                                           data-product_id="${item.product.id}">&times;</a>
                                    </td>
                                    <td class="product-thumbnail">
                                        <a href="#!">
                                            <img width="57" height="70" src="assets/img/shop/prd_01.jpg"
                                                 class="attachment-shop_thumbnail size-shop_thumbnail wp-post-image"
                                                 alt="#!" />
                                        </a>
                                    </td>
                                    <td class="product-name" data-title="Product">
                                        <a href="#!">${item.product.name}</a>
                                    </td>
                                    <td class="product-price" data-title="Price">
                                        <span class="woocommerce-Price-amount amount">
                                            <span class="woocommerce-Price-currencySymbol">$</span>${item.product.price}
                                        </span>
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
                                            <span class="woocommerce-Price-currencySymbol">$</span>${itemTotal.toFixed(2)}
                                        </span>
                                    </td>

                                </tr>
                            `);
                            total += itemTotal;
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

            $.ajax({
                url: '{{ route('cart.update') }}',
                method: 'POST',
                data: {
                    product_id: productId,
                    quantity: quantity,
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
        $(document).on('click', '.remove', function() {
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


<!-- Mirrored from html.xpressbuddy.com/purefit/cart.html by HTTrack Website Copier/3.x [XR&CO'2014], Sat, 27 Jul 2024 09:34:27 GMT -->
</html>





