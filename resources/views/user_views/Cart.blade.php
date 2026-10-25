@extends('layouts.store')

@section('title', 'Cart')
@section('robots', 'noindex, follow')
@section('page', 'cart')

@section('content')
    <!-- main area start  -->
    <main>
        <!-- breadcrumb start -->
        <section class="breadcrumb position-bottom bg_img" data-background="{{ asset('assets/img/bg/shop-cart-banner.webp')}}">
            <div class="container">
                <div class="breadcrumb__content text-center">
                    <h2 class="breadcrumb__title" style="text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7); color: #fff;">Cart</h2>
                    <ul class="breadcrumb__list clearfix">
                        <li class="breadcrumb-item">
                            <a href="{{route('home')}}" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); color: #fff;">
                                Supplement Station
                            </a>
                        </li>
                        <li class="breadcrumb-item" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); color: #fff;">
                            Cart
                        </li>
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
                                                <input type="hidden" name="shipping_method[0]" data-index="0"
                                                       id="shipping_method_0" value="free_shipping:1"
                                                       class="shipping_method" />
                                                <form class="woocommerce-shipping-calculator"
                                                      action="http://localhost/wp/?page_id=5" method="post">
                                                    <p><a href="#!" class="shipping-calculator-button">To Be Announced
                                                            </a></p>
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

                                        <tr class="discount-row" style="display: none;">
                                            <th>
                                                <i class="fa fa-tag" style="margin-right: 5px;"></i>
                                                Coupon Discount
                                            </th>
                                            <td>-$<span id="discount-amount">0.00</span></td>
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
@endsection
