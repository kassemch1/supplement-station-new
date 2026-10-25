// Cart page. The lines, totals and coupon box are drawn by store.js (SS.cart, SS.coupon) like in the
// cart drawer; this page only adds the "complete your stack" row under the cart.
$(function () {
    'use strict';

    $(document).on('cart:updated', function () {
        SS.suggestions.load();
    });
});
