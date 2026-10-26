/*
 * The parts of the admin template's main.js the pages use (the rest was demo code: a theme customizer
 * added to every page, to-do and chat widgets, tooltips, a custom scrollbar). Runs before admin-ui.js.
 */
(function ($) {
    'use strict';

    // AJAX calls send the CSRF token
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});

    // Side menu: open on wide screens, closed on narrow ones; the header button and the × toggle it
    // (only when the width crosses 1200 px, so a phone's address bar showing or hiding leaves it alone)
    var $window = $(window), $side = $('.side-header'), wasWide = null;
    function fit() {
        var wide = $window.width() >= 1200;
        if (wide !== wasWide) {
            $side.toggleClass('show', wide).toggleClass('hide', !wide);
            wasWide = wide;
        }
    }
    fit();
    $window.on('resize', fit);
    $('.side-header-toggle').on('click', function () {
        var open = $side.hasClass('show');
        $side.toggleClass('show', !open).toggleClass('hide', open);
    });
    $('.side-header-close').on('click', function () { $side.removeClass('show').addClass('hide'); });

    // Arrow on the menu groups (admin-ui.js opens and closes them)
    $('.side-header-menu .side-header-sub-menu').siblings('a')
        .append('<span class="menu-expand"><i class="zmdi zmdi-chevron-down"></i></span>');
})(jQuery);
