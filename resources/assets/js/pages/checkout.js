// Checkout page: order summary, coupon box and placing the order
$(function () {
    'use strict';

    function loadSummary() {
        $.get(SS.routes.cart).done(function (response) {
            var $rows = $('#order_review tbody').empty();
            var subtotal = 0;

            if (!response.items.length) {
                $rows.append('<tr><td colspan="2" style="text-align: center; padding: 30px;">Your cart is empty.</td></tr>');
                $('#place_order_btn').prop('disabled', true);
                $('#cart_empty_message').show();
                return;
            }

            $('#place_order_btn').prop('disabled', false);
            $('#cart_empty_message').hide();
            response.items.forEach(function (item) {
                var itemTotal = SS.priceAfterDiscount(item.product) * item.quantity;
                $rows.append(
                    '<tr class="cart_single">' +
                        '<td class="product-name">' +
                            SS.esc(item.product.name) + ' <br/>' +
                            '<small>Options: ' + SS.esc(item.formatted_options) + '</small>' +
                            '<strong class="product-quantity">&times; ' + item.quantity + '</strong>' +
                        '</td>' +
                        '<td class="product-total"><span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol">$</span>' + itemTotal.toFixed(2) + '</span></td>' +
                    '</tr>'
                );
                subtotal += itemTotal;
            });
            $('#checkout-subtotal').text(subtotal.toFixed(2));
            checkAppliedCouponAndUpdateTotals(subtotal);
        });
    }

    // Removing something from the header mini cart updates the summary
    $(document).on('cart:changed', loadSummary);

    /* ---------- coupon ---------- */

    function displayMessage(message, type) {
        var $box = $('#coupon-message');
        $box.text(message).removeClass('success error').addClass(type || 'success').show();
        setTimeout(function () { $box.fadeOut(); }, 5000);
    }

    function updateCheckoutTotal(finalTotal) {
        $('#checkout-total').text(finalTotal.toFixed(2));
    }

    function showCoupon(coupon, discountAmount) {
        $('#coupon-name').text(coupon.name);
        $('#coupon-discount').text(coupon.discount_percentage + '%');
        $('#discount-amount').text(discountAmount.toFixed(2));
        $('#applied-coupon').show();
        $('.discount-row').show();
    }

    function hideCoupon() {
        $('#applied-coupon').hide();
        $('.discount-row').hide();
        $('#discount-amount').text('0.00');
    }

    // The discount is worked out again from the current subtotal
    function checkAppliedCouponAndUpdateTotals(subtotal) {
        $.get(SS.routes.couponApplied)
            .done(function (response) {
                if (!response.success) {
                    updateCheckoutTotal(subtotal);
                    return;
                }
                var discountAmount = (subtotal * response.coupon.discount_percentage) / 100;
                showCoupon(response.coupon, discountAmount);
                updateCheckoutTotal(subtotal - discountAmount);
                $('#coupon-code').val(response.coupon.code).prop('disabled', true);
                $('#apply-coupon').hide();
                $('#remove-coupon').show();
            })
            .fail(function () { updateCheckoutTotal(subtotal); });
    }

    $('#apply-coupon').on('click', function () {
        var code = $('#coupon-code').val().trim();
        if (!code) {
            displayMessage('Please enter a coupon code.', 'error');
            return;
        }
        $(this).prop('disabled', true).text('Applying...');

        $.post(SS.routes.couponApply, {coupon_code: code})
            .done(function (response) {
                if (response.success) {
                    showCoupon(response.coupon, response.coupon.discount_amount);
                    updateCheckoutTotal(response.final_total);
                    displayMessage(response.message, 'success');
                    $('#coupon-code').val('').prop('disabled', true);
                    $('#apply-coupon').hide();
                    $('#remove-coupon').show();
                }
            })
            .fail(function (xhr) {
                displayMessage((xhr.responseJSON || {}).message || 'Failed to apply coupon.', 'error');
            })
            .always(function () {
                $('#apply-coupon').prop('disabled', false).text('Apply');
            });
    });

    $('#remove-coupon').on('click', function () {
        $.post(SS.routes.couponRemove).done(function (response) {
            if (response.success) {
                hideCoupon();
                displayMessage(response.message, 'success');
                $('#coupon-code').val('').prop('disabled', false);
                $('#apply-coupon').show();
                $('#remove-coupon').hide();
                updateCheckoutTotal(parseFloat($('#checkout-subtotal').text()));
            }
        });
    });

    /* ---------- place the order ---------- */

    $('#checkout_form').on('submit', function (e) {
        e.preventDefault();
        var $button = $('#place_order_btn');
        if ($button.data('submitting')) {
            return; // already placing this order
        }
        $button.data('submitting', true).prop('disabled', true).text('Placing your order...');

        $.ajax({
            url: SS.routes.placeOrder,
            method: 'POST',
            data: $(this).serialize(),
            complete: function (xhr) {
                if (!(xhr.responseJSON && xhr.responseJSON.success)) {
                    $button.data('submitting', false).prop('disabled', false).text('Place Order');
                }
            },
            success: function (response) {
                if (response.success) {
                    $button.hide();
                    $('#success_message').text('Your order #' + response.order_id + ' has been placed successfully!').show();
                    $('.mini-cart-count').text('0');
                    $('.header-mini-cart').empty();
                    if (response.billing_email) {
                        $('#success_message').append('<p>Check your email for your receipt! 😊</p>');
                    }
                }
            },
            error: function (xhr) {
                $('.error-message').empty();
                if (xhr.status === 422 && !(xhr.responseJSON && xhr.responseJSON.errors)) {
                    alert((xhr.responseJSON && xhr.responseJSON.message) || 'Please check your details and try again.');
                } else if (xhr.status === 422) {
                    $.each(xhr.responseJSON.errors, function (key, value) {
                        $('#' + key + '_error').text(value);
                    });
                } else {
                    alert('Failed to place the order. Please try again.');
                }
            }
        });
    });

    loadSummary();
});
