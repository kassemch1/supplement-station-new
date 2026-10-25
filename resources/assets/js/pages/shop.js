// Shop page: categories, brands, filters, sorting and "load more" without reloading the page.
// Every button is a normal link or form (works without this script); here they are loaded in place,
// the address bar follows, and Back/Forward work.
$(function () {
    'use strict';

    var $results = $('#shop-results');
    var request = null;

    function skeleton(n) {
        return new Array(n + 1).join('<div class="ss-skel-card"></div>');
    }

    function render(response, url, push) {
        $('#shop-nav').html(response.nav);
        $('#shop-sidebar').html(response.sidebar);
        $results.html(response.results).removeClass('is-loading');
        document.title = response.title;
        if (push) {
            history.pushState({shop: true}, '', url);
        }
        keepActiveInView();
    }

    function load(url, push) {
        if (request) {
            request.abort();
        }
        $results.addClass('is-loading');
        closeFilters();
        request = $.ajax({url: url, dataType: 'json', headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .done(function (response) {
                render(response, url, push);
                var top = $results.offset().top - 90;
                if (window.scrollY > top) {
                    window.scrollTo({top: Math.max(0, $('#shop-nav').offset().top - 70), behavior: 'smooth'});
                }
            })
            .fail(function (xhr, status) {
                if (status !== 'abort') {
                    window.location.href = url;
                }
            })
            .always(function () { request = null; });
    }

    $(document).on('click', '#shop-app [data-shop-link], #shop-sidebar [data-shop-link]', function (e) {
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.which === 2) {
            return;
        }
        e.preventDefault();
        load(this.href, true);
    });

    $(document).on('submit', '[data-shop-form]', function (e) {
        e.preventDefault();
        var fields = $(this).serializeArray().filter(function (f) { return f.value !== '' && f.value !== 'default'; });
        var url = this.action + (fields.length ? '?' + $.param(fields) : '');
        load(url, true);
    });
    $(document).on('change', '[data-autosubmit]', function () {
        $(this).closest('form').trigger('submit');
    });

    // The header search on phones searches inside the shop page without reloading it
    $(document).on('submit', '[data-app-search]', function (e) {
        e.preventDefault();
        var q = $(this).find('input[name="q"]').val().trim();
        load(this.action + (q ? '?q=' + encodeURIComponent(q) : ''), true);
        $(this).find('input').blur();
    });

    window.addEventListener('popstate', function () {
        load(window.location.href, false);
    });

    /* ---------- load more (button, and automatically when it comes near the screen) ---------- */

    var loadingMore = false;

    function loadMore($link) {
        if (loadingMore) {
            return;
        }
        loadingMore = true;
        var $grid = $('#shop-grid');
        var $skeleton = $(skeleton(4));
        $grid.append($skeleton);
        $link.addClass('is-disabled').text('Loading…');
        $.ajax({url: $link.attr('href'), dataType: 'json', headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .done(function (response) {
                $skeleton.remove();
                $grid.append(response.cards);
                $results.find('[data-shown]').text(response.to);
                if (response.next) {
                    $link.attr('href', response.next).removeClass('is-disabled').text('Load more products');
                } else {
                    $link.closest('.ss-more').remove();
                }
                loadingMore = false;
                // Still near the bottom (a tall screen, or the visitor kept scrolling): the next page too
                checkLoadMore();
            })
            .fail(function () {
                $skeleton.remove();
                $link.removeClass('is-disabled').text('Load more products');
                loadingMore = false;
            });
    }

    $(document).on('click', '[data-load-more]', function (e) {
        e.preventDefault();
        loadMore($(this));
    });

    // Loads the next page when the button is less than about a screen below the visible part, so
    // products are there before the visitor reaches the end. Checked on scroll, at most once a frame.
    function checkLoadMore() {
        var link = document.querySelector('[data-load-more]');
        if (!link || loadingMore || link.classList.contains('is-disabled')) {
            return;
        }
        if (link.getBoundingClientRect().top < window.innerHeight + 700) {
            loadMore($(link));
        }
    }

    var checkQueued = false;
    function queueCheck() {
        if (!checkQueued) {
            checkQueued = true;
            requestAnimationFrame(function () {
                checkQueued = false;
                checkLoadMore();
            });
        }
    }
    window.addEventListener('scroll', queueCheck, {passive: true});
    window.addEventListener('resize', queueCheck);
    /* ---------- filters sheet (phones) ---------- */

    function closeFilters() {
        $('body').removeClass('ss-filters-open ss-lock');
    }
    $(document).on('click', '[data-open-filters]', function () {
        $('body').addClass('ss-filters-open ss-lock');
    });
    $(document).on('click', '[data-close-filters]', closeFilters);

    // Brand search inside the filters (searches the hidden brands too)
    $(document).on('input', '[data-brand-find]', function () {
        var q = this.value.trim().toLowerCase();
        $(this).closest('.ss-fgroup').find('[data-brand-list]').toggleClass('is-searching', q !== '');
        $(this).closest('.ss-fgroup').find('[data-brand-name]').each(function () {
            this.hidden = q !== '' && this.getAttribute('data-brand-name').indexOf(q) === -1;
        });
    });

    // "Show all brands" / "Show fewer brands"
    $(document).on('click', '[data-brand-more]', function () {
        var open = $(this).closest('.ss-fgroup').find('[data-brand-list]').toggleClass('is-all').hasClass('is-all');
        $(this).toggleClass('is-open', open).contents().first()[0].textContent = (open ? this.dataset.less : this.dataset.more) + ' ';
    });

    // The chosen category and subcategory stay visible in their scrolling rows
    function keepActiveInView() {
        $('.ss-cats__track, .ss-subs__track, .ss-brandbar__track').each(function () {
            var active = this.querySelector('.is-active');
            if (active && active.offsetLeft > 0) {
                this.scrollLeft = active.offsetLeft - (this.clientWidth - active.clientWidth) / 2;
            }
        });
    }

    history.replaceState({shop: true}, '', window.location.href);
    keepActiveInView();
});
