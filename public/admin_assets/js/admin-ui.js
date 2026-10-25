/*
 * Admin chrome, loaded on every admin page after main.js:
 *  1. the side menu opens and closes quickly (the template animated it for 400 ms)
 *  2. the page you are on is marked in the menu
 *  3. dark / light switch (dark is the default; the choice is kept in this browser)
 *  4. lists, filters and actions without page reloads, searchable dropdowns (Select2)
 *  5. the new-order bell: pending count, latest pending orders, a sound, a desktop alert and a
 *     pop-up when an order comes in, and the "orders waiting" bar at the top of each page
 */
(function ($) {
    'use strict';

    var store = {
        get: function (key) { try { return localStorage.getItem(key); } catch (e) { return null; } },
        set: function (key, value) { try { localStorage.setItem(key, value); } catch (e) {} }
    };

    /* ---------- 1. side menu ---------- */
    var $nav = $('.side-header-menu');
    var SPEED = 160;
    $nav.off('click');                                       // drop the template's slow handler
    $nav.find('.side-header-sub-menu').stop(true, true).hide();

    function setChevron($li, open) {
        $li.children('a').find('.menu-expand i')
            .toggleClass('zmdi-chevron-up', open)
            .toggleClass('zmdi-chevron-down', !open);
    }

    $nav.on('click', 'li.has-sub-menu > a', function (e) {
        e.preventDefault();
        var $li = $(this).parent();
        var open = !$li.children('ul').is(':visible');
        $li.siblings('.has-sub-menu').removeClass('active').each(function () {
            $(this).children('ul').stop(true, true).slideUp(SPEED);
            setChevron($(this), false);
        });
        $li.toggleClass('active', open).children('ul').stop(true, true)[open ? 'slideDown' : 'slideUp'](SPEED);
        setChevron($li, open);
    });

    /* ---------- 2. current page in the menu ---------- */
    // exact page first, else the list it belongs to (/manageProducts/2/edit -> /manageProducts)
    (function () {
        var here = location.pathname, best = null, bestLen = 0;
        $nav.find('.side-header-sub-menu a').each(function () {
            var path = this.pathname;
            if (path === here) { best = this; return false; }
            if (path !== '/admin' && here.indexOf(path + '/') === 0 && path.length > bestLen) { best = this; bestLen = path.length; }
        });
        if (!best) return;
        var $li = $(best).closest('li').addClass('current');
        var $group = $li.closest('.has-sub-menu').addClass('active');
        $group.children('ul').show();
        setChevron($group, true);
    })();

    /* ---------- 3. dark / light ---------- */
    var root = document.documentElement;
    $('#adThemeToggle').on('click', function () {
        var next = root.getAttribute('data-theme') === 'light' ? 'dark' : 'light';
        root.setAttribute('data-theme', next);
        store.set('admin-theme', next);
        document.dispatchEvent(new CustomEvent('admin-theme-change', {detail: next}));
    });

    /* ---------- 4. lists without page reloads ----------
     * Markup hooks (the pages' buttons and fields stay as they were):
     *   data-ajax-list="name"  a part of the page that is reloaded in place after an action, a
     *                          filter or a page change (the same part is taken from the fresh page)
     *   data-ajax-filter       a GET filter form: applies as you type / pick, keeps the address,
     *                          gets a "Clear filters" button (or uses a [data-ajax-clear] link in it)
     *   data-ajax-form         a POST/PUT/DELETE form sent in the background; its success or error
     *                          message pops up and the lists refresh
     *   data-no-select2        a <select> that stays a plain browser select
     * Pages call AdminUI.refresh() instead of location.reload().
     */
    var AdminUI = window.AdminUI = window.AdminUI || {};

    function toast(message, type) {
        if (!message) return;
        if (type === 'error') $('.modal.show').modal('hide');   // a refused "are you sure?" closes
        var $t = $('<div class="ad-toast ad-toast--' + (type || 'success') + '" role="status"></div>');
        $('<span class="ad-toast__icon"></span>').text(type === 'error' ? '!' : '✓').appendTo($t);
        $('<span class="ad-toast__text"></span>').append($('<strong></strong>').text(message)).appendTo($t);
        $('<button type="button" class="ad-toast__close" aria-label="Close">&times;</button>').appendTo($t)
            .on('click', function () { $t.remove(); });
        $('#adToasts').append($t);
        setTimeout(function () { $t.addClass('is-leaving'); setTimeout(function () { $t.remove(); }, 400); }, type === 'error' ? 8000 : 3500);
    }
    AdminUI.toast = toast;

    // Searchable dropdowns everywhere
    function initSelects(root) {
        if (!$.fn.select2) return;
        $(root || document).find('.content-body select, .modal select').not('[data-no-select2], .select2-hidden-accessible').each(function () {
            var $s = $(this), $modal = $s.closest('.modal');
            var first = $s.find('option').first();
            $s.select2({
                // filter bars without a grid column get a fixed width, everything else fills its column
                width: $s.closest('form[data-ajax-filter], .form-inline').length && !$s.parent().is('[class*="col-"]') ? '210px' : '100%',
                placeholder: first.val() === '' ? first.text() : undefined,
                dropdownParent: $modal.length ? $modal : $(document.body),
                minimumResultsForSearch: 0
            });
        });
    }
    AdminUI.initSelects = initSelects;

    function swapLists(doc) {
        var swapped = 0;
        $('[data-ajax-list]').each(function () {
            var fresh = doc.querySelector('[data-ajax-list="' + this.getAttribute('data-ajax-list') + '"]');
            if (!fresh) return;
            $(this).find('select.select2-hidden-accessible').select2('destroy');
            this.innerHTML = fresh.innerHTML;
            initSelects(this);
            swapped++;
        });
        $(document).trigger('admin:refreshed');
        return swapped;
    }

    // success / error boxes the server put on the page become pop-ups
    function toastAlerts(doc) {
        var shown = function () { return !/display:\s*none/.test(this.getAttribute('style') || '') && $.trim($(this).text()) !== ''; };
        $(doc).find('.content-body .alert-success, .content-body .so-alert-success').filter(shown).each(function () { toast($.trim($(this).text())); });
        $(doc).find('.content-body .alert-danger, .content-body .so-alert-error').not('.ad-static').filter(shown).each(function () { toast($.trim($(this).text()), 'error'); });
    }

    var loading = null;
    // Reload the marked parts of the page from url (default: this address)
    AdminUI.refresh = function (url, opts) {
        opts = opts || {};
        url = url || location.href;
        if (!$('[data-ajax-list]').length) { location.href = url; return $.Deferred().resolve(); }
        $('.modal.show').modal('hide');
        if (loading) loading.abort();
        $('[data-ajax-list]').addClass('is-loading');
        loading = $.ajax({url: url, dataType: 'html'})
            .done(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                if (opts.push) history.pushState({adminList: true}, '', url);
                swapLists(doc);
                if (opts.alerts) toastAlerts(doc);
            })
            .fail(function (xhr) { if (xhr.statusText !== 'abort') toast('Could not load the list. Please try again.', 'error'); })
            .always(function () { loading = null; $('[data-ajax-list]').removeClass('is-loading'); });
        if (AdminUI.refreshBell) AdminUI.refreshBell();
        return loading;
    };

    window.addEventListener('popstate', function () { if ($('[data-ajax-list]').length) AdminUI.refresh(location.href); });

    // Page links inside a list
    $(document).on('click', '[data-ajax-list] .pagination a[href]', function (e) {
        e.preventDefault();
        AdminUI.refresh(this.href, {push: true});
        var top = $(this).closest('[data-ajax-list]').offset().top - 90;
        if (window.scrollY > top) window.scrollTo({top: top, behavior: 'smooth'});
    });

    // Filters
    // the fields that differ from their cleared state (empty, or a select's data-clear-to)
    function activeFields(form) {
        return $(form).serializeArray().filter(function (p) {
            var keep = $(form).find('select[name="' + p.name + '"]').data('clear-to');
            return p.value !== '' && p.name !== '_token' && (keep === undefined || String(keep) !== p.value);
        });
    }
    function filterUrl(form) {
        var params = activeFields(form);
        var base = form.getAttribute('action') || location.pathname;
        return base.split('?')[0] + (params.length ? '?' + $.param(params) : '');
    }
    function hasFilters(form) {
        return activeFields(form).length > 0;
    }
    function syncClear(form) {
        $(form).find('[data-ajax-clear]').toggleClass('is-idle', !hasFilters(form));
    }
    function applyFilter(form) {
        syncClear(form);
        AdminUI.refresh(filterUrl(form), {push: true});
    }
    var typing = null;
    $('form[data-ajax-filter]').each(function () {
        var form = this;
        if (!$(form).find('[data-ajax-clear]').length) {
            $('<button type="button" class="btn btn-secondary ad-clear" data-ajax-clear><i class="zmdi zmdi-close"></i> Clear filters</button>')
                .appendTo($(form).find('[data-clear-slot]').first().length ? $(form).find('[data-clear-slot]').first() : $(form));
        }
        syncClear(form);
    });
    $(document).on('submit', 'form[data-ajax-filter]', function (e) {
        e.preventDefault();
        clearTimeout(typing);
        applyFilter(this);
    });
    $(document).on('change', 'form[data-ajax-filter] select', function () { applyFilter(this.form); });
    $(document).on('input', 'form[data-ajax-filter] input[type="text"], form[data-ajax-filter] input[type="search"]', function () {
        var form = this.form;
        clearTimeout(typing);
        typing = setTimeout(function () { applyFilter(form); }, 450);
    });
    $(document).on('click', 'form[data-ajax-filter] [data-ajax-clear]', function (e) {
        e.preventDefault();
        var form = $(this).closest('form')[0];
        clearTimeout(typing);
        $(form).find('input[type="text"], input[type="search"]').val('');
        $(form).find('select').each(function () {
            var $s = $(this), keep = $s.data('clear-to');
            $s.val(keep !== undefined ? String(keep) : $s.find('option').first().val());
            if ($s.hasClass('select2-hidden-accessible')) $s.trigger('change.select2');
        });
        applyFilter(form);
    });

    // Action forms sent in the background
    $(document).on('submit', 'form[data-ajax-form]', function (e) {
        if (e.isDefaultPrevented()) return;          // an onsubmit="return confirm(...)" said no
        e.preventDefault();
        var form = this, $btn = $(form).find('[type="submit"]').prop('disabled', true), raw = null;
        $.ajax({
            url: form.action,
            type: 'POST',                             // _method in the form says PUT / PATCH / DELETE
            data: new FormData(form),
            processData: false,
            contentType: false,
            dataType: 'html',
            headers: {Accept: 'application/json, text/html;q=0.9'},
            xhr: function () { raw = new XMLHttpRequest(); return raw; }
        }).done(function (html, status, xhr) {
            $('.modal.show').modal('hide');
            var type = xhr.getResponseHeader('Content-Type') || '';
            if (type.indexOf('json') !== -1) {
                var data = {};
                try { data = JSON.parse(html); } catch (err) {}
                var said = data.message || (typeof data.success === 'string' ? data.success : null);
                toast(said || form.getAttribute('data-success') || 'Saved.', data.success === false || data.status === 'error' ? 'error' : 'success');
                AdminUI.refresh();
                return;
            }
            // the server answered with a page (it redirected): show its message; when it is this very
            // page (same filters) take the new lists from it, otherwise reload them for this address
            var doc = new DOMParser().parseFromString(html, 'text/html');
            var hadMessage = $(doc).find('.content-body .alert, .content-body .so-alert').not('.ad-static').filter(function () { return !/display:\s*none/.test(this.getAttribute('style') || ''); }).length > 0;
            toastAlerts(doc);
            if (!hadMessage) toast(form.getAttribute('data-success') || 'Saved.');
            var landed = raw && raw.responseURL;
            if (landed && landed === location.href && swapLists(doc)) {
                if (AdminUI.refreshBell) AdminUI.refreshBell();
            } else {
                AdminUI.refresh();
            }
        }).fail(function (xhr) {
            var msg = 'Something went wrong. Please try again.';
            try {
                var data = JSON.parse(xhr.responseText);
                msg = data.errors ? Object.values(data.errors)[0][0] : (data.message || msg);
            } catch (err) {}
            toast(msg, 'error');
        }).always(function () { $btn.prop('disabled', false); });
    });

    $(function () { initSelects(document); });

    /* ---------- 5. new orders ---------- */
    var body = document.body;
    var summaryUrl = body.getAttribute('data-pending-url');
    if (!summaryUrl) return;

    var POLL_MS = 20000;
    var $bellBtn = $('#adBellBtn'), $menu = $('#adBellMenu'), $list = $('#adBellList');
    var $count = $('#adBellCount'), $menuCounts = $('[data-pending-count]');
    var $bar = $('#adPendingBar'), $barText = $('#adPendingBarText');
    var $sound = $('#adSoundToggle'), $desktop = $('#adDesktopAlerts');
    var baseTitle = document.title, flashTimer = null, timer = null;
    var onPendingList = location.pathname === new URL(body.getAttribute('data-pending-list'), location.href).pathname;

    $sound.prop('checked', store.get('admin-order-sound') !== 'off');
    $sound.on('change', function () {
        store.set('admin-order-sound', this.checked ? 'on' : 'off');
        if (this.checked) unlockAudio();
    });

    // Bell menu
    $bellBtn.on('click', function (e) {
        e.stopPropagation();
        var open = $menu.prop('hidden');
        $menu.prop('hidden', !open);
        $bellBtn.attr('aria-expanded', open);
        if (open) refresh();
    });
    $(document).on('click', function (e) {
        if (!$(e.target).closest('#adBell').length) { $menu.prop('hidden', true); $bellBtn.attr('aria-expanded', false); }
    });
    $(document).on('keydown', function (e) { if (e.key === 'Escape') $menu.prop('hidden', true); });

    // Sound: browsers only allow it after the admin has clicked or typed once on the page
    var audio = null;
    function unlockAudio() {
        try {
            audio = audio || new (window.AudioContext || window.webkitAudioContext)();
            if (audio.state === 'suspended') audio.resume();
        } catch (e) {}
    }
    $(document).one('pointerdown keydown', unlockAudio);

    // A loud three-note ring repeated for about 10 seconds; any click or key press on the page stops it
    var RINGS = 8, RING_EVERY = 1.25;
    var ringing = [];
    function stopChime() {
        ringing.forEach(function (osc) { try { osc.stop(); } catch (e) {} });
        ringing = [];
        $(document).off('.adChime');
    }
    function chime() {
        if (!$sound.prop('checked') || !audio) return;
        try {
            if (audio.state === 'suspended') audio.resume();
            stopChime();
            // the compressor lets it be loud without crackling
            var out = audio.createDynamicsCompressor();
            out.threshold.value = -10;
            out.ratio.value = 4;
            out.connect(audio.destination);
            var t = audio.currentTime + 0.05;
            for (var r = 0; r < RINGS; r++) {
                [[988, 0], [1319, 0.18], [1760, 0.36]].forEach(function (n) {
                    var at = t + r * RING_EVERY + n[1];
                    // two voices per note (a bright one and a clean one) carry further than one
                    [['square', 0.22], ['sine', 0.9]].forEach(function (v) {
                        var osc = audio.createOscillator(), gain = audio.createGain();
                        osc.type = v[0];
                        osc.frequency.value = n[0];
                        gain.gain.setValueAtTime(0.0001, at);
                        gain.gain.exponentialRampToValueAtTime(v[1], at + 0.015);
                        gain.gain.exponentialRampToValueAtTime(0.0001, at + 0.5);
                        osc.connect(gain).connect(out);
                        osc.start(at);
                        osc.stop(at + 0.55);
                        ringing.push(osc);
                    });
                });
            }
            setTimeout(function () { $(document).one('pointerdown.adChime keydown.adChime', stopChime); }, 600);
        } catch (e) {}
    }
    $('#adSoundTest').on('click', function () { unlockAudio(); var was = $sound.prop('checked'); $sound.prop('checked', true); chime(); $sound.prop('checked', was); });

    // Desktop alerts (shown even when the admin is on another tab)
    function desktopSupported() { return 'Notification' in window && window.isSecureContext; }
    function showDesktopButton() { $desktop.prop('hidden', !(desktopSupported() && Notification.permission === 'default')); }
    showDesktopButton();
    $desktop.on('click', function () { Notification.requestPermission().then(showDesktopButton); });

    // "a · b · c" with each piece isolated, so an Arabic city or name keeps its place
    function parts(list) {
        var out = [];
        list.filter(Boolean).forEach(function (p, i) {
            if (i) out.push(document.createTextNode(' · '));
            out.push($('<bdi></bdi>').text(p)[0]);
        });
        return out;
    }

    // Pop-ups in the corner
    function orderToast(order) {
        var $t = $('<a class="ad-toast" role="status"></a>').attr('href', order.url);
        $('<span class="ad-toast__icon"></span>').text('$').appendTo($t);
        var $txt = $('<span class="ad-toast__text"></span>').appendTo($t);
        $('<strong></strong>').text('New order #' + order.id + ' · ' + order.total).appendTo($txt);
        $('<span></span>').append(parts([order.name, order.city, order.ago])).appendTo($txt);
        var $close = $('<button type="button" class="ad-toast__close" aria-label="Close">&times;</button>').appendTo($t);
        $close.on('click', function (e) { e.preventDefault(); $t.remove(); });
        $('#adToasts').append($t);
        // stays until the admin opens or closes it
    }

    function flashTitle(text) {
        clearInterval(flashTimer);
        var on = false;
        flashTimer = setInterval(function () { on = !on; document.title = on ? text : baseTitle; }, 1000);
    }
    $(window).on('focus', function () { clearInterval(flashTimer); flashTimer = null; render.last && render(render.last); });

    function render(data) {
        render.last = data;
        var n = data.pending;
        $count.text(n > 99 ? '99+' : n).prop('hidden', n === 0);
        $menuCounts.text(n).prop('hidden', n === 0);
        $bellBtn.toggleClass('has-pending', n > 0);
        if (!flashTimer) document.title = (n ? '(' + n + ') ' : '') + baseTitle;

        $bar.prop('hidden', n === 0 || onPendingList);
        $barText.text(n === 1 ? '1 order is waiting to be delivered.' : n + ' orders are waiting to be delivered.');

        $list.empty();
        if (!data.latest_pending.length) {
            $list.append('<p class="ad-bell__empty">No pending orders. All caught up.</p>');
        }
        data.latest_pending.forEach(function (o) {
            var $a = $('<a class="ad-bell__item"></a>').attr('href', o.url);
            var $l = $('<span class="ad-bell__main"></span>').appendTo($a);
            $('<strong></strong>').append(parts(['#' + o.id, o.name])).appendTo($l);
            $('<span></span>').append(parts([o.city, o.ago])).appendTo($l);
            $('<span class="ad-bell__total"></span>').text(o.total).appendTo($a);
            $list.append($a);
        });
    }

    function announce(orders) {
        // newest last, at most 3 pop-ups
        orders.slice(0, 3).reverse().forEach(orderToast);
        chime();
        flashTitle(orders.length === 1 ? '🔔 New order!' : '🔔 ' + orders.length + ' new orders!');
        if (desktopSupported() && Notification.permission === 'granted') {
            var o = orders[0];
            try {
                var note = new Notification('New order #' + o.id + ' · ' + o.total, {body: o.name + (o.city ? ' · ' + o.city : ''), tag: 'order-' + o.id});
                note.onclick = function () { window.focus(); location.href = o.url; };
            } catch (e) {}
        }
    }

    function refresh() {
        var seen = parseInt(store.get('admin-last-order-id') || '0', 10);
        $.ajax({url: summaryUrl, data: seen ? {after: seen} : {}, dataType: 'json', headers: {Accept: 'application/json'}})
            .done(function (data) {
                render(data);
                // another open admin tab may already have rung for these
                var seenNow = parseInt(store.get('admin-last-order-id') || '0', 10);
                var fresh = (data.new_orders || []).filter(function (o) { return o.id > seenNow; });
                if (data.latest_id > seenNow) store.set('admin-last-order-id', data.latest_id);
                if (seen && fresh.length) announce(fresh);
            })
            .fail(function (xhr) {
                if (xhr.status === 401 || xhr.status === 419) clearInterval(timer);   // logged out
            });
    }

    AdminUI.refreshBell = refresh;
    refresh();
    timer = setInterval(refresh, POLL_MS);
})(jQuery);

// let the menu slide from now on (not while the page was loading)
requestAnimationFrame(function () { requestAnimationFrame(function () { document.body.classList.add('ad-ready'); }); });
