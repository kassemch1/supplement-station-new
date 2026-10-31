/*
 * The parts of the admin template's main.js the pages use (the rest was demo code: a theme customizer
 * added to every page, to-do and chat widgets, tooltips, a custom scrollbar). Runs before admin-ui.js.
 */
(function ($) {
    'use strict';

    // AJAX calls send the CSRF token
    $.ajaxSetup({headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')}});

    // Side menu: open on wide screens, closed on narrow ones; the header button and the × toggle it
    // (only when the width crosses 1200 px, so a phone's address bar showing or hiding leaves it alone).
    // On narrow screens the open menu lies over the page: the page behind is dimmed and holds still, so a
    // swipe scrolls the menu, and a tap on the dimmed part closes it.
    var $window = $(window), $side = $('.side-header'), wasWide = null;
    var $backdrop = $('<div class="ad-side-backdrop"></div>').appendTo(document.body);
    function setMenu(open) {
        $side.toggleClass('show', open).toggleClass('hide', !open);
        $(document.documentElement).toggleClass('ad-menu-open', open && !wasWide);
    }
    function fit() {
        var wide = $window.width() >= 1200;
        if (wide !== wasWide) {
            wasWide = wide;
            setMenu(wide);
        }
    }
    fit();
    $window.on('resize', fit);
    $('.side-header-toggle').on('click', function () { setMenu(!$side.hasClass('show')); });
    $('.side-header-close').add($backdrop).on('click', function () { setMenu(false); });

    // Arrow on the menu groups (admin-ui.js opens and closes them)
    $('.side-header-menu .side-header-sub-menu').siblings('a')
        .append('<span class="menu-expand"><i class="zmdi zmdi-chevron-down"></i></span>');
})(jQuery);
