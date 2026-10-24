@extends('layouts.store')

@section('title', 'Checkout')
@section('robots', 'noindex, follow')
@section('page', 'checkout')

@section('content')
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
                            {{-- Same token on every submit of this page, so a double-click or retry can't create a second order --}}
                            <input type="hidden" name="checkout_token" value="{{ \Illuminate\Support\Str::uuid() }}">

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
@endsection
