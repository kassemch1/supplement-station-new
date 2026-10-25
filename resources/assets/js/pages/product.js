// Product page: pictures, picking options, quantity, adding to the cart, writing a review
$(function () {
    'use strict';

    var $form = $('#add-to-cart-form');
    var selectedOptions = {};

    function message(text, isError) {
        $form.find('[data-pdp-msg]').text(text || '').toggleClass('is-error', !!isError);
    }

    /* ---------- pictures: swipe/scroll between them, thumbnails and dots follow ---------- */

    var main = document.querySelector('[data-pdp-main]');
    var slides = main ? main.querySelectorAll('[data-pdp-slide]') : [];

    function showSlide(index, smooth) {
        if (!slides[index]) {
            return;
        }
        main.scrollTo({left: slides[index].offsetLeft - main.offsetLeft, behavior: smooth === false ? 'auto' : 'smooth'});
        markSlide(index);
    }

    function markSlide(index) {
        $('[data-pdp-thumb]').removeClass('is-active').filter('[data-pdp-thumb="' + index + '"]').addClass('is-active');
        $('.ss-pdp__dots span').removeClass('is-active').eq(index).addClass('is-active');
    }

    $(document).on('click', '[data-pdp-thumb]', function () {
        showSlide(parseInt(this.getAttribute('data-pdp-thumb'), 10));
    });

    if (main && slides.length > 1) {
        var scrollTimer = null;
        main.addEventListener('scroll', function () {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(function () {
                markSlide(Math.round(main.scrollLeft / main.clientWidth));
            }, 60);
        }, {passive: true});
    }

    // The flavour/colour picture, when the admin added one, takes the place of the first picture
    function showOptionImage(src) {
        var first = slides[0];
        if (!first) {
            return;
        }
        if (!first.dataset.original) {
            first.dataset.original = first.src;
        }
        first.src = src;
        showSlide(0);
    }

    /* ---------- options ---------- */

    $(document).on('click', '.option-value', function (event) {
        event.preventDefault();
        var name = this.dataset.optionName;
        var value = this.dataset.optionValue;

        if (selectedOptions[name] === value) {
            delete selectedOptions[name];
            this.classList.remove('is-selected');
        } else {
            $('.option-value').filter(function () { return this.dataset.optionName === name; }).removeClass('is-selected');
            selectedOptions[name] = value;
            this.classList.add('is-selected');
            if (this.dataset.image) {
                showOptionImage(this.dataset.image);
            }
        }
        $('[data-option-chosen]').filter(function () { return this.dataset.optionChosen === name; })
            .text(selectedOptions[name] || 'Choose');
        $(this).closest('.ss-opt').removeClass('is-missing');

        $('#selected_options').val(JSON.stringify(selectedOptions));
        message('');
    });

    /* ---------- quantity ---------- */

    var $quantity = $('#quantity');
    function setQuantity(value) {
        $quantity.val(Math.min(100, Math.max(1, parseInt(value, 10) || 1)));
    }
    $(document).on('click', '[data-pdp-step]', function () {
        setQuantity((parseInt($quantity.val(), 10) || 1) + parseInt(this.getAttribute('data-pdp-step'), 10));
    });
    $quantity.on('change', function () { setQuantity(this.value); });

    /* ---------- add to cart (the button on the page or the one in the bar at the bottom on phones) ---------- */

    $form.on('submit', function (e) {
        e.preventDefault();

        // Every option (flavour, size...) has to be chosen first
        var missing = $form.find('[data-option-group]').filter(function () {
            return !selectedOptions[this.getAttribute('data-option-group')];
        });
        if (missing.length) {
            missing.addClass('is-missing');
            message('Please choose a ' + missing.first().attr('data-option-group').toLowerCase() + ' first.', true);
            missing[0].scrollIntoView({behavior: 'smooth', block: 'center'});
            return;
        }

        var $buttons = $('button[type="submit"]').filter(function () {
            return this.form === $form[0];
        }).prop('disabled', true);

        SS.cart.add($form.find('[name="product_id"]').val(), $quantity.val(), selectedOptions)
            .done(function () { message(''); })
            .fail(function (xhr) {
                message(SS.errorMessage(xhr, 'Could not add this product. Please try again.'), true);
            })
            .always(function () { $buttons.prop('disabled', false); });
    });

    /* ---------- long descriptions fold ---------- */

    var text = document.querySelector('[data-pdp-text]');
    if (text && text.scrollHeight > 260) {
        text.classList.add('is-folded');
        $('[data-pdp-readmore]').prop('hidden', false).on('click', function () {
            text.classList.remove('is-folded');
            this.hidden = true;
        });
    }

    /* ---------- reviews ---------- */

    $('#reviewForm').on('submit', function (event) {
        event.preventDefault();
        var form = this;
        var $done = $('#successMessage').removeClass('is-error').text('');
        var $button = $(form).find('button[type="submit"]').prop('disabled', true);

        fetch(form.dataset.url, {
            method: 'POST',
            body: new FormData(form),
            headers: {'X-CSRF-TOKEN': SS.csrf, 'Accept': 'application/json'}
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.success) {
                    $done.addClass('is-error').text((data && data.message) || 'Please fill in your name, a rating and your review.');
                    return;
                }
                $done.text(data.message);
                form.reset();
                var review = data.review;
                var rating = parseInt(review.rating, 10) || 0;
                $('[data-no-reviews]').remove();
                $('#reviewsSection').prepend(
                    '<article class="ss-review">' +
                        '<span class="ss-review__avatar">' + SS.esc(String(review.name).trim().charAt(0).toUpperCase()) + '</span>' +
                        '<div>' +
                            '<div class="ss-review__top"><strong>' + SS.esc(review.name) + '</strong>' +
                            '<span class="ss-stars ss-stars--sm" style="--rating: ' + rating + '" aria-label="' + rating + ' out of 5"></span></div>' +
                            '<p>' + SS.esc(review.message) + '</p>' +
                        '</div>' +
                    '</article>'
                );
            })
            .catch(function () { $done.addClass('is-error').text('Could not post your review. Please try again.'); })
            .finally(function () { $button.prop('disabled', false); });
    });
});
