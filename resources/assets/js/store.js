// Shared code for every store page: header mini cart, newsletter popup, footer contact form and the
// "someone in ... purchased" toast. Each page used to carry its own copy of this. URLs and the CSRF token
// come from window.SS, which layouts/store.blade.php writes before the scripts load.
(function ($) {
    'use strict';

    var SS = window.SS;

    // Absolute URL for a path the server gives without the domain (e.g. product_image "storage/x.webp")
    SS.url = function (path) {
        path = String(path || '');
        return /^(https?:)?\/\//.test(path) ? path : SS.baseUrl + '/' + path.replace(/^\//, '');
    };

    SS.esc = function (text) {
        return String(text == null ? '' : text).replace(/[&<>"']/g, function (c) {
            return {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c];
        });
    };

    SS.priceAfterDiscount = function (product) {
        var discount = product.discount || 0;
        return discount ? product.price * (1 - discount / 100) : product.price;
    };

    // Pages may replace this to show messages their own way (the cart page does)
    SS.notify = function (message) {
        alert(message);
    };

    $.ajaxSetup({headers: {'X-CSRF-TOKEN': SS.csrf}});

    /* ---------- header mini cart ---------- */

    SS.miniCart = {
        load: function (open) {
            $.get(SS.routes.cart).done(function (response) {
                SS.miniCart.render(response.items || []);
                if (open) {
                    $('.header-mini-cart').show();
                }
            });
        },

        render: function (items) {
            var $box = $('.header-mini-cart').empty();
            var total = 0;
            var count = 0;

            if (!items.length) {
                $box.append('<p>Your cart is empty.</p>');
            }
            items.forEach(function (item) {
                if (!item.product) {
                    return;
                }
                var price = SS.priceAfterDiscount(item.product);
                total += price * item.quantity;
                count += item.quantity;
                $box.append(
                    '<div class="woocommerce-mini-cart-item d-flex align-items-center" style="padding: 10px;">' +
                        '<div class="mini-cart-img" style="margin-right: 10px;">' +
                            (item.product_image ? '<img src="' + SS.esc(SS.url(item.product_image)) + '" alt="' + SS.esc(item.product.name) + '" style="width: 50px; height: 50px; object-fit: cover;" loading="lazy">' : '') +
                        '</div>' +
                        '<div class="mini-cart-content" style="flex-grow: 1;">' +
                            '<h4 class="product-title" style="margin: 0; font-size: 14px;">' +
                                '<a href="' + SS.url('products/' + item.product.id) + '" style="text-decoration: none; color: #000;">' + SS.esc(item.product.name) + '</a>' +
                            '</h4>' +
                            '<div class="mini-cart-price" style="margin-top: 5px;">' +
                                item.quantity + ' × <span class="woocommerce-Price-amount amount" style="color: red;">$' + price.toFixed(2) + '</span>' +
                            '</div>' +
                        '</div>' +
                        '<div class="remove-button" style="margin-left: auto;">' +
                            '<a href="#" class="mini-cart-remove remove_from_cart_button" data-product_id="' + item.product.id + '" data-cart_item_id="' + item.id + '" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: lightgrey; text-align: center; line-height: 20px; color: red; font-size: 14px;">×</a>' +
                        '</div>' +
                    '</div>'
                );
            });
            if (items.length) {
                $box.append(
                    '<p class="woocommerce-mini-cart__total"><strong>Subtotal:</strong> <span class="woocommerce-Price-amount">$' + total.toFixed(2) + '</span></p>' +
                    '<p class="checkout-link">' +
                        '<a href="' + SS.routes.cartPage + '" class="button wc-forward">View cart</a>' +
                        '<a href="' + SS.routes.checkoutPage + '" class="button checkout wc-forward">Checkout</a>' +
                    '</p>'
                );
            }
            $('.mini-cart-count').text(count);
        }
    };

    // Removes a cart line from anywhere on the page, then tells the page the cart changed
    SS.removeCartItem = function (productId, cartItemId) {
        return $.post(SS.routes.cartRemove, {product_id: productId, cart_item_id: cartItemId})
            .done(function (response) {
                if (response.success) {
                    SS.cartChanged(response.message);
                } else {
                    SS.notify('Failed to remove item from cart.');
                }
            })
            .fail(function (xhr) {
                SS.notify((xhr.responseJSON && xhr.responseJSON.message) || 'Failed to remove item from cart.');
            });
    };

    // The cart and checkout pages listen to 'cart:changed' to redraw their own tables
    SS.cartChanged = function (message) {
        SS.miniCart.load();
        $(document).trigger('cart:changed', [message]);
    };

    $(document).on('click', '.mini-cart-remove', function (event) {
        event.preventDefault();
        SS.removeCartItem($(this).data('product_id'), $(this).data('cart_item_id'));
    });

    $(function () {
        $('.header-shop-cart > a').on('click', function () {
            $(this).siblings('.header-mini-cart').toggle();
        });
        SS.miniCart.load();
    });

    /* ---------- newsletter popup (pages that include partials/subscription-modal) ---------- */

    $(function () {
        var $modal = $('#subscriptionModal');
        if (!$modal.length) {
            return;
        }

        var close = function () {
            $.post(SS.routes.subscriptionCancel).always(function () {
                $modal.fadeOut();
            });
        };

        $('#closeModal, #cancelSubscription').on('click', close);
        $modal.find('.subscription-modal-overlay').on('click', function (e) {
            if (e.target === this) {
                close();
            }
        });

        $('#subscriptionForm').on('submit', function (e) {
            e.preventDefault();
            var $button = $(this).find('.btn-subscribe');
            var label = $button.text();
            var $message = $('#subscriptionMessage');
            $button.text('Processing...').prop('disabled', true);

            $.post(SS.routes.subscribe, {email: $('#subscriptionEmail').val()})
                .done(function (response) {
                    if (response.success) {
                        $message.html('<div style="color: green;">' + SS.esc(response.message) + '</div>').show();
                        setTimeout(function () { $modal.fadeOut(); }, 2000);
                    } else {
                        $message.html('<div style="color: red;">' + SS.esc(response.message) + '</div>').show();
                        $('#subscriptionEmail').val('').attr('placeholder', 'Try a different email address').focus();
                    }
                })
                .fail(function (xhr) {
                    var error = (xhr.responseJSON && xhr.responseJSON.message) || 'An error occurred. Please try again.';
                    $message.html('<div style="color: red;">' + SS.esc(error) + '</div>').show();
                })
                .always(function () {
                    $button.text(label).prop('disabled', false);
                });
        });

        setTimeout(function () {
            $.get(SS.routes.subscriptionCheck).done(function (response) {
                if (response.show_modal) {
                    $modal.fadeIn();
                }
            });
        }, $modal.data('delay') || 3000);
    });

    /* ---------- footer contact form ---------- */

    $(document).on('submit', '#contact_form', function (e) {
        e.preventDefault();
        // The product page has alerts with the same ids, so look only inside the footer
        var $footer = $(this).closest('footer');
        var flash = function (id) {
            var $alert = $footer.find(id).fadeIn();
            setTimeout(function () { $alert.fadeOut(); }, 3000);
        };
        $.post($(this).attr('action'), $(this).serialize())
            .done(function () {
                flash('#successAlert');
            })
            .fail(function (xhr, status, error) {
                flash('#errorAlert');
                alert(error);
            });
    });

    /* ---------- "someone in ... purchased" toast ---------- */

    // Starts after the page has finished loading so it never competes with the page itself
    $(window).on('load', function () {
        var toast = document.getElementById('rpToast');
        if (!toast) {
            return;
        }
        var imgEl = document.getElementById('rpToastImg');
        var closeBtn = document.getElementById('rpToastClose');
        var purchases = [];
        var idx = 0;
        var currentUrl = '#';
        var hideTimer = null;
        var nextTimer = null;
        var dismissed = false;
        var SHOW_MS = 10000;
        var GAP_MS = 15000;
        var FIRST_DELAY_MS = 4000;

        function show(item) {
            currentUrl = item.product_url || '#';
            document.getElementById('rpToastCity').textContent = item.city || 'Lebanon';
            document.getElementById('rpToastProduct').textContent = item.product_name || '';
            document.getElementById('rpToastTime').textContent = item.time_ago || 'recently';
            if (item.image) {
                imgEl.src = item.image;
                imgEl.style.display = '';
            } else {
                imgEl.style.display = 'none';
            }
            toast.classList.add('rp-toast-visible');
        }

        function loop() {
            if (dismissed || !purchases.length) {
                return;
            }
            show(purchases[idx++ % purchases.length]);
            clearTimeout(hideTimer);
            clearTimeout(nextTimer);
            hideTimer = setTimeout(function () {
                toast.classList.remove('rp-toast-visible');
                nextTimer = setTimeout(loop, GAP_MS);
            }, SHOW_MS);
        }

        toast.addEventListener('click', function (e) {
            if (e.target !== closeBtn && currentUrl !== '#') {
                window.location.href = currentUrl;
            }
        });
        closeBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            dismissed = true;
            clearTimeout(hideTimer);
            clearTimeout(nextTimer);
            toast.classList.remove('rp-toast-visible');
        });

        fetch(SS.routes.recentPurchases, {headers: {'Accept': 'application/json'}})
            .then(function (r) { return r.ok ? r.json() : []; })
            .then(function (data) {
                purchases = Array.isArray(data) ? data : [];
                // shuffle once so the order isn't always the same on reload
                for (var i = purchases.length - 1; i > 0; i--) {
                    var j = Math.floor(Math.random() * (i + 1));
                    var t = purchases[i]; purchases[i] = purchases[j]; purchases[j] = t;
                }
                if (purchases.length) {
                    setTimeout(loop, FIRST_DELAY_MS);
                }
            })
            .catch(function () { /* silent */ });
    });
})(jQuery);
