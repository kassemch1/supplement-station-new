// Shared code for every store page: the cart (drawer, lines, totals, suggestions), the quick view, the
// newsletter popup, the footer contact form and the "someone in ... purchased" toast. URLs and the CSRF
// token come from window.SS, which layouts/store.blade.php writes before the scripts load.
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


    SS.money = function (n) {
        return '$' + (Math.round((parseFloat(n) || 0) * 100) / 100).toFixed(2);
    };

    var ICON = {
        plus: '<svg class="ss-ui" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>',
        minus: '<svg class="ss-ui" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14"/></svg>',
        trash: '<svg class="ss-ui" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>',
        bag: '<svg class="ss-ui" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>'
    };
    SS.icon = ICON;

    /* ---------- toast ---------- */

    var toastTimer;
    SS.toast = function (message, isError) {
        var el = document.getElementById('ss-toast');
        if (!el) {
            return;
        }
        el.textContent = message;
        el.classList.toggle('is-error', !!isError);
        el.classList.add('is-visible');
        clearTimeout(toastTimer);
        toastTimer = setTimeout(function () { el.classList.remove('is-visible'); }, 2800);
    };
    SS.notify = function (message) {
        SS.toast(message, true);
    };

    function errorMessage(xhr, fallback) {
        return (xhr && xhr.responseJSON && (xhr.responseJSON.message || (xhr.responseJSON.errors && Object.values(xhr.responseJSON.errors)[0][0]))) || fallback;
    }
    SS.errorMessage = errorMessage;

    function lockScroll(lock) {
        document.body.classList.toggle('ss-lock', lock);
    }

    /* ---------- cart: one place that loads, changes and draws it ---------- */

    // The cart lines as the drawer, the cart page and the checkout show them
    SS.cart = {
        data: null,
        request: null,

        refresh: function () {
            if (this.request) {
                this.request.abort();
            }
            this.request = $.ajax({url: SS.routes.cart, dataType: 'json', cache: false});
            return this.request.done(function (response) {
                SS.cart.data = response;
                SS.cart.request = null;
                SS.cart.draw();
                $(document).trigger('cart:updated', [response]);
            });
        },

        count: function () {
            return this.data && this.data.summary ? this.data.summary.count : 0;
        },

        draw: function () {
            var data = this.data || {items: [], summary: {count: 0, subtotal: 0, discount: 0, total: 0}};
            var count = data.summary ? data.summary.count : 0;

            $('[data-cart-count]').text(count);
            $('.ss-tabbar__badge[data-cart-count]').prop('hidden', count === 0);
            $('.mini-cart-count').text(count);
            $('[data-cart-count-label]').text(count ? '(' + count + ')' : '');

            var $lines = $('[data-cart-lines]').empty();
            if (!data.items.length) {
                $lines.html(
                    '<div class="ss-cart-empty">' +
                        '<span class="ss-cart-empty__icon">' + ICON.bag + '</span>' +
                        '<h3>Your cart is empty</h3><p>Find your next favourite supplement.</p>' +
                        '<a href="' + SS.url('shop') + '" class="ss-btn ss-btn--dark">Start shopping</a>' +
                    '</div>'
                );
                $('[data-cart-foot]').prop('hidden', true);
            } else {
                data.items.forEach(function (item) {
                    $lines.append(SS.cart.lineHtml(item));
                });
                $('[data-cart-foot]').prop('hidden', false);
            }
            SS.cart.drawSummary($(document), data.summary);
        },

        drawSummary: function ($scope, summary) {
            summary = summary || {subtotal: 0, discount: 0, total: 0};
            $scope.find('[data-sum-subtotal]').text(SS.money(summary.subtotal));
            $scope.find('[data-sum-total]').text(SS.money(summary.total));
            var hasDiscount = summary.discount > 0;
            $scope.find('[data-sum-discount-row]').prop('hidden', !hasDiscount);
            $scope.find('[data-sum-discount]').text('-' + SS.money(summary.discount));
            if (summary.coupon) {
                $scope.find('[data-sum-coupon]').text('Coupon ' + summary.coupon.code);
            }
            $('[data-checkout-link]').toggleClass('is-disabled', !!summary.has_problems);
        },

        lineHtml: function (item) {
            var product = item.product || {};
            var url = item.product_url || SS.url('products/' + encodeURIComponent(product.slug || product.id));
            var image = item.product_image ? SS.url(item.product_image) : '';
            return '' +
                '<div class="ss-line" data-line="' + item.id + '" data-product="' + product.id + '">' +
                    '<a href="' + SS.esc(url) + '" class="ss-line__img">' + (image ? '<img src="' + SS.esc(image) + '" alt="" loading="lazy">' : '') + '</a>' +
                    '<div>' +
                        '<div class="ss-line__top">' +
                            '<div>' +
                                '<a href="' + SS.esc(url) + '" class="ss-line__name">' + SS.esc(product.name) + '</a>' +
                                (item.formatted_options ? '<div class="ss-line__opts">' + SS.esc(item.formatted_options) + '</div>' : '') +
                            '</div>' +
                            '<button type="button" class="ss-line__remove" data-line-remove aria-label="Remove ' + SS.esc(product.name) + '">' + ICON.trash + '</button>' +
                        '</div>' +
                        (item.unavailable_reason ? '<div class="ss-line__warn">' + SS.esc(item.unavailable_reason) + ' Please remove it.</div>' : '') +
                        '<div class="ss-line__bottom">' +
                            '<div class="ss-qty">' +
                                '<button type="button" data-qty-step="-1" aria-label="Less">' + ICON.minus + '</button>' +
                                '<input type="number" min="1" max="100" value="' + item.quantity + '" data-qty-input aria-label="Quantity">' +
                                '<button type="button" data-qty-step="1" aria-label="More">' + ICON.plus + '</button>' +
                            '</div>' +
                            '<span class="ss-line__total">' + SS.money(item.subtotal) + '</span>' +
                        '</div>' +
                    '</div>' +
                '</div>';
        },

        // Adds a product (with the options picked) and opens the drawer on it
        add: function (productId, quantity, options) {
            return $.post(SS.routes.cartAdd, {
                product_id: productId,
                quantity: quantity || 1,
                selected_options: JSON.stringify(options || {})
            }).done(function () {
                SS.cart.open(productId);
                $('.ss-cartpill').removeClass('is-bump');
                setTimeout(function () { $('.ss-cartpill').addClass('is-bump'); }, 10);
            });
        },

        setQuantity: function (lineId, quantity) {
            var $line = $('[data-line="' + lineId + '"]').addClass('is-busy');
            return $.post(SS.routes.cartUpdate, {cart_item_id: lineId, quantity: quantity})
                .fail(function (xhr) { SS.toast(errorMessage(xhr, 'Could not update the cart.'), true); })
                .always(function () { SS.cartChanged(); $line.removeClass('is-busy'); });
        },

        remove: function (lineId) {
            var $line = $('[data-line="' + lineId + '"]').addClass('is-busy');
            return $.post(SS.routes.cartRemove, {cart_item_id: lineId})
                .done(function () { $line.slideUp(160); })
                .fail(function (xhr) { $line.removeClass('is-busy'); SS.toast(errorMessage(xhr, 'Could not remove the item.'), true); })
                .always(function () { SS.cartChanged(); });
        },

        open: function (highlightProductId) {
            var $drawer = $('#cart-drawer');
            $drawer.addClass('is-open').attr('aria-hidden', 'false');
            lockScroll(true);
            this.refresh().done(function () {
                if (highlightProductId) {
                    $drawer.find('[data-product="' + highlightProductId + '"]').last().addClass('is-new');
                }
                SS.suggestions.load();
            });
            setTimeout(function () { $drawer.find('.ss-drawer__head .ss-iconbtn').trigger('focus'); }, 50);
        },

        close: function () {
            $('#cart-drawer').removeClass('is-open').attr('aria-hidden', 'true');
            if (!$('#quick-view').hasClass('is-open')) {
                lockScroll(false);
            }
        }
    };

    // Pages written before the drawer call these
    SS.miniCart = {
        load: function (open) {
            return open ? SS.cart.open() : SS.cart.refresh();
        }
    };
    SS.removeCartItem = function (productId, cartItemId) {
        return SS.cart.remove(cartItemId);
    };
    // The cart and checkout pages listen to 'cart:changed' / 'cart:updated' to redraw
    SS.cartChanged = function (message) {
        $(document).trigger('cart:changed', [message]);
        return SS.cart.refresh();
    };

    // Quantity buttons and inputs on any cart line (drawer or cart page); typing waits a moment
    var qtyTimers = {};
    $(document).on('click', '[data-qty-step]', function () {
        var $line = $(this).closest('[data-line]');
        var $input = $line.find('[data-qty-input]');
        var value = Math.min(100, Math.max(1, (parseInt($input.val(), 10) || 1) + parseInt($(this).data('qty-step'), 10)));
        if (value === parseInt($input.val(), 10)) {
            return;
        }
        $input.val(value);
        var lineId = $line.data('line');
        clearTimeout(qtyTimers[lineId]);
        qtyTimers[lineId] = setTimeout(function () { SS.cart.setQuantity(lineId, value); }, 350);
    });
    $(document).on('change', '[data-qty-input]', function () {
        var value = Math.min(100, Math.max(1, parseInt(this.value, 10) || 1));
        this.value = value;
        SS.cart.setQuantity($(this).closest('[data-line]').data('line'), value);
    });
    $(document).on('click', '[data-line-remove]', function () {
        SS.cart.remove($(this).closest('[data-line]').data('line'));
    });

    $(document).on('click', '[data-open-cart], .header-shop-cart > a', function (e) {
        e.preventDefault();
        SS.cart.open();
    });
    $(document).on('click', '[data-close-cart]', function () {
        SS.cart.close();
    });
    $(document).on('click', '[data-checkout-link].is-disabled', function (e) {
        e.preventDefault();
        SS.toast('Something in your cart is sold out. Please remove it first.', true);
    });
    $(document).on('keydown', function (e) {
        if (e.key === 'Escape') {
            if ($('#quick-view').hasClass('is-open')) {
                SS.quickView.close();
            } else if ($('#cart-drawer').hasClass('is-open')) {
                SS.cart.close();
            }
        }
    });

    /* ---------- "complete your stack" suggestions ---------- */

    SS.suggestions = {
        loadedFor: null,

        load: function (force) {
            var key = (SS.cart.data && SS.cart.data.items || []).map(function (i) { return i.product_id; }).join(',');
            if (!force && key === this.loadedFor) {
                return;
            }
            this.loadedFor = key;
            $.getJSON(SS.routes.cartSuggestions).done(function (response) {
                SS.suggestions.draw($('[data-suggest]'), response.products || []);
                $(document).trigger('suggestions:loaded', [response.products || []]);
            });
        },

        cardHtml: function (p) {
            return '<article class="ss-mini">' +
                '<a href="' + SS.esc(p.url) + '" class="ss-mini__media">' +
                    (p.image ? '<img src="' + SS.esc(p.image) + '" alt="" loading="lazy">' : '') +
                    (p.discount > 0 ? '<span class="ss-tag ss-tag--red">-' + Math.round(p.discount) + '%</span>' : '') +
                '</a>' +
                '<div class="ss-mini__body">' +
                    '<a href="' + SS.esc(p.url) + '" class="ss-mini__name">' + SS.esc(p.name) + '</a>' +
                    '<div class="ss-mini__row">' +
                        '<span class="ss-price"><span class="ss-price__now">' + SS.money(p.final_price) + '</span></span>' +
                        '<button type="button" class="ss-mini__add" data-suggest-add="' + p.id + '" data-has-options="' + (p.has_options ? 1 : 0) + '" aria-label="Add ' + SS.esc(p.name) + '">' + ICON.plus + '</button>' +
                    '</div>' +
                '</div>' +
            '</article>';
        },

        draw: function ($section, products) {
            $section.each(function () {
                var $track = $(this).find('[data-suggest-track]').empty();
                products.forEach(function (p) { $track.append(SS.suggestions.cardHtml(p)); });
                $(this).prop('hidden', !products.length);
            });
        }
    };

    $(document).on('click', '[data-suggest-add]', function () {
        var $button = $(this);
        if (String($button.data('has-options')) === '1') {
            SS.quickView.open($button.data('suggest-add'));
            return;
        }
        $button.prop('disabled', true);
        SS.cart.add($button.data('suggest-add'), 1, {})
            .done(function () { SS.toast('Added to your cart'); })
            .fail(function (xhr) { SS.toast(errorMessage(xhr, 'Could not add this product.'), true); })
            .always(function () { $button.prop('disabled', false); });
    });
    $(document).on('click', '[data-suggest-scroll]', function () {
        var track = $(this).closest('[data-suggest], .ss-suggest').find('[data-suggest-track]')[0];
        if (track) {
            track.scrollBy({left: parseInt($(this).data('suggest-scroll'), 10) * 300, behavior: 'smooth'});
        }
    });

    // Rows of product cards that scroll sideways (home, product page): the arrows move them a few cards
    $(document).on('click', '[data-rail-scroll]', function () {
        var track = $(this).closest('[data-rail]').find('[data-rail-track]')[0];
        if (track) {
            track.scrollBy({left: parseInt($(this).data('rail-scroll'), 10) * track.clientWidth * 0.8, behavior: 'smooth'});
        }
    });

    /* ---------- coupon box (cart and checkout pages) ---------- */

    // The totals come from the server (SS.cart.data.summary), so the box only sends the code and redraws
    SS.coupon = {
        message: function (text, isError) {
            $('[data-coupon-msg]').text(text || '').toggleClass('is-error', !!isError);
        },
        draw: function (summary) {
            var coupon = summary && summary.coupon;
            $('[data-coupon-form]').prop('hidden', !!coupon);
            $('[data-coupon-applied]').prop('hidden', !coupon);
            if (coupon) {
                $('[data-coupon-code]').text(coupon.code);
                $('[data-coupon-name]').text(coupon.discount_percentage ? '· ' + parseFloat(coupon.discount_percentage) + '% off' : '');
                if (coupon.problem) {
                    SS.coupon.message(coupon.problem, true);
                }
            }
        }
    };
    $(document).on('cart:updated', function (e, data) {
        if ($('[data-coupon]').length) {
            SS.coupon.draw(data.summary);
        }
    });
    $(document).on('click', '[data-coupon-apply]', function () {
        var $input = $(this).closest('[data-coupon]').find('[data-coupon-input]');
        var code = $.trim($input.val());
        if (!code) {
            SS.coupon.message('Please enter a coupon code.', true);
            $input.trigger('focus');
            return;
        }
        var $button = $(this).prop('disabled', true).text('…');
        $.post(SS.routes.couponApply, {coupon_code: code})
            .done(function (response) {
                SS.coupon.message(response.message, false);
                $input.val('');
                SS.cart.refresh();
            })
            .fail(function (xhr) { SS.coupon.message(errorMessage(xhr, 'This coupon could not be applied.'), true); })
            .always(function () { $button.prop('disabled', false).text('Apply'); });
    });
    $(document).on('keydown', '[data-coupon-input]', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $(this).closest('[data-coupon]').find('[data-coupon-apply]').trigger('click');
        }
    });
    $(document).on('click', '[data-coupon-remove]', function () {
        $.post(SS.routes.couponRemove).always(function () {
            SS.coupon.message('');
            SS.cart.refresh();
        });
    });

    /* ---------- quick view ---------- */

    SS.quickView = {
        product: null,
        chosen: {},

        open: function (productId) {
            var $qv = $('#quick-view');
            $qv.find('[data-qv-content]').html('<div class="ss-qv__loading"><span class="ss-spin"></span></div>');
            $qv.addClass('is-open').attr('aria-hidden', 'false');
            lockScroll(true);
            $.getJSON(SS.routes.quickView.replace('__ID__', productId))
                .done(function (product) {
                    SS.quickView.product = product;
                    SS.quickView.chosen = {};
                    // An option with a single value in stock is picked already
                    product.options.forEach(function (group) {
                        var available = group.values.filter(function (v) { return v.in_stock; });
                        if (available.length === 1 && group.values.length === 1) {
                            SS.quickView.chosen[group.name] = available[0].value;
                        }
                    });
                    SS.quickView.draw();
                })
                .fail(function () {
                    SS.quickView.close();
                    SS.toast('This product could not be loaded.', true);
                });
        },

        close: function () {
            $('#quick-view').removeClass('is-open').attr('aria-hidden', 'true');
            if (!$('#cart-drawer').hasClass('is-open')) {
                lockScroll(false);
            }
        },

        missing: function () {
            var chosen = this.chosen;
            return (this.product.options || []).filter(function (g) { return !chosen[g.name]; }).map(function (g) { return g.name; });
        },

        draw: function () {
            var p = this.product;
            var chosen = this.chosen;
            var images = p.images.length ? p.images : (p.image ? [p.image] : []);
            var meta = [p.brand, p.category].filter(Boolean).join(' · ');

            var gallery = '<div class="ss-qv__gallery"><div class="ss-qv__main" data-qv-main>' +
                images.map(function (src, i) { return '<img src="' + SS.esc(src) + '" alt="' + SS.esc(p.name) + ' ' + (i + 1) + '"' + (i ? ' loading="lazy"' : '') + '>'; }).join('') +
                '</div>' +
                (images.length > 1 ? '<div class="ss-qv__thumbs">' + images.map(function (src, i) {
                    return '<button type="button" data-qv-thumb="' + i + '" class="' + (i ? '' : 'is-active') + '"><img src="' + SS.esc(src) + '" alt=""></button>';
                }).join('') + '</div>' : '') +
                '</div>';

            var options = (p.options || []).map(function (group) {
                return '<div class="ss-opt"><span class="ss-opt__label">' + SS.esc(group.name) + ': <em>' + SS.esc(chosen[group.name] || 'Choose') + '</em></span>' +
                    '<div class="ss-opt__values">' + group.values.map(function (v) {
                        var cls = 'ss-optbtn' + (v.image ? ' has-img' : '') + (chosen[group.name] === v.value ? ' is-selected' : '') + (v.in_stock ? '' : ' is-out');
                        return '<button type="button" class="' + cls + '" data-qv-opt="' + SS.esc(group.name) + '" data-value="' + SS.esc(v.value) + '"' +
                            (v.image ? ' data-image="' + SS.esc(v.image) + '"' : '') + (v.in_stock ? '' : ' disabled aria-disabled="true" title="Sold out"') + '>' +
                            (v.image ? '<img src="' + SS.esc(v.image) + '" alt="" loading="lazy">' : '') + SS.esc(v.value) + '</button>';
                    }).join('') + '</div></div>';
            }).join('');

            var missing = this.missing();
            var buttonLabel = !p.in_stock ? 'Sold out' : (missing.length ? 'Choose ' + missing[0] : 'Add to cart · ' + SS.money(p.final_price));

            $('#quick-view [data-qv-content]').html(
                '<div class="ss-qv__grid">' + gallery +
                '<div class="ss-qv__info">' +
                    (meta ? '<div class="ss-qv__meta">' + SS.esc(meta) + '</div>' : '') +
                    '<h2 class="ss-qv__name">' + SS.esc(p.name) + '</h2>' +
                    '<div class="ss-qv__price ss-price"><span class="ss-price__now">' + SS.money(p.final_price) + '</span>' +
                        (p.discount > 0 ? '<s class="ss-price__was">' + SS.money(p.price) + '</s><span class="ss-tag ss-tag--soft">Save ' + Math.round(p.discount) + '%</span>' : '') + '</div>' +
                    options +
                    '<p class="ss-qv__msg" data-qv-msg></p>' +
                    '<div class="ss-qv__buy">' +
                        '<div class="ss-qty ss-qty--lg"><button type="button" data-qv-step="-1" aria-label="Less">' + ICON.minus + '</button><input type="number" min="1" max="100" value="1" data-qv-qty aria-label="Quantity"><button type="button" data-qv-step="1" aria-label="More">' + ICON.plus + '</button></div>' +
                        '<button type="button" class="ss-btn ss-btn--red" data-qv-add' + (!p.in_stock || missing.length ? ' disabled' : '') + '>' + ICON.bag + ' ' + SS.esc(buttonLabel) + '</button>' +
                    '</div>' +
                    (p.description ? '<p class="ss-qv__desc">' + SS.esc(p.description) + '</p>' : '') +
                    '<a href="' + SS.esc(p.url) + '" class="ss-qv__more">View full details</a>' +
                '</div></div>'
            );
        },

        showImage: function (src) {
            var $main = $('[data-qv-main]');
            var $img = $main.find('img').filter(function () { return this.getAttribute('src') === src; }).first();
            if (!$img.length) {
                $img = $('<img alt="">').attr('src', src);
                $main.prepend($img);
            }
            $main[0].scrollTo({left: $img[0].offsetLeft, behavior: 'smooth'});
        }
    };

    $(document).on('click', '[data-quick]', function (e) {
        e.preventDefault();
        SS.quickView.open($(this).data('quick'));
    });
    $(document).on('click', '[data-close-qv]', function () {
        SS.quickView.close();
    });
    $(document).on('click', '[data-qv-opt]', function () {
        var qv = SS.quickView;
        var name = $(this).data('qv-opt');
        var value = String($(this).data('value'));
        var image = $(this).data('image');
        var qty = $('[data-qv-qty]').val();
        qv.chosen[name] = value;
        var scroll = $('#quick-view .ss-qv__panel').scrollTop();
        qv.draw();
        $('[data-qv-qty]').val(qty);
        $('#quick-view .ss-qv__panel').scrollTop(scroll);
        if (image) {
            qv.showImage(image);
        }
    });
    $(document).on('click', '[data-qv-thumb]', function () {
        var $main = $('[data-qv-main]');
        var img = $main.find('img').eq($(this).data('qv-thumb'))[0];
        $(this).addClass('is-active').siblings().removeClass('is-active');
        if (img) {
            $main[0].scrollTo({left: img.offsetLeft, behavior: 'smooth'});
        }
    });
    $(document).on('click', '[data-qv-step]', function () {
        var $input = $('[data-qv-qty]');
        $input.val(Math.min(100, Math.max(1, (parseInt($input.val(), 10) || 1) + parseInt($(this).data('qv-step'), 10))));
    });
    $(document).on('click', '[data-qv-add]', function () {
        var qv = SS.quickView;
        var $button = $(this).prop('disabled', true);
        SS.cart.add(qv.product.id, parseInt($('[data-qv-qty]').val(), 10) || 1, qv.chosen)
            .done(function () { qv.close(); })
            .fail(function (xhr) {
                $('[data-qv-msg]').text(errorMessage(xhr, 'Could not add this product.'));
                $button.prop('disabled', false);
            });
    });

    $(function () {
        SS.cart.refresh();
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
