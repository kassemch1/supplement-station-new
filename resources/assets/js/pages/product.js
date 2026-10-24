// Product page: picking options, adding to the cart, writing a review
$(function () {
    'use strict';

    var $form = $('#add-to-cart-form');
    var $main = $('main'); // the footer has alerts with the same ids
    var selectedOptions = {};

    var flash = function (id, text) {
        var $alert = $main.find(id).text(text).fadeIn();
        setTimeout(function () { $alert.fadeOut(); }, 3000);
    };

    $('.option-value').on('click', function (event) {
        event.preventDefault();
        var name = this.dataset.optionName;
        var value = this.dataset.optionValue;

        if (selectedOptions[name] === value) {
            delete selectedOptions[name];
            this.classList.remove('selected');
            this.style.color = '';
        } else {
            $('.option-value').filter(function () { return this.dataset.optionName === name; })
                .removeClass('selected').css('color', '');
            selectedOptions[name] = value;
            this.classList.add('selected');
            this.style.color = 'white';
        }

        $('#selected_options').val(JSON.stringify(selectedOptions));
        $form.find('.add-to-cart').prop('disabled', false);
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            type: 'POST',
            url: $form.attr('action'),
            data: new FormData(this),
            processData: false,
            contentType: false
        }).done(function (response) {
            flash('#successAlert', response.message);
            SS.miniCart.load(true);
            $('.header-mini-cart').addClass('visible');
            setTimeout(function () { $('.header-mini-cart').removeClass('visible'); }, 5000);
        }).fail(function (xhr) {
            flash('#errorAlert', (xhr.responseJSON && xhr.responseJSON.message) || 'Please select an option before adding item to cart.');
        });
    });

    $('#quantity').on('change', function () {
        var quantity = parseInt(this.value, 10);
        if (isNaN(quantity) || quantity < 1) {
            this.value = 1;
        }
    });

    /* ---------- reviews ---------- */

    var avatar = SS.url('assets/img/icon/profile.png');

    $('#reviewForm').on('submit', function (event) {
        event.preventDefault();
        var form = this;

        fetch(form.dataset.url, {
            method: 'POST',
            body: new FormData(form),
            headers: {'X-CSRF-TOKEN': SS.csrf, 'Accept': 'application/json'}
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (!data.success) {
                    alert('An error occurred. Please try again.');
                    return;
                }
                $('#successMessage').text(data.message).show();
                form.reset();
                resetStars();
                $('#reviewsSection').prepend(
                    '<div class="client-rv">' +
                        '<div class="d-md-none"><img src="' + avatar + '" alt="Client Avatar" style="height: 60px;width:60px"></div>' +
                        '<div class="client-pic d-none d-md-block"><img src="' + avatar + '" alt="Client Avatar" style="width:100px;height:60px"></div>' +
                        '<div class="details">' +
                            '<div class="name-rating-time" style="display: flex; flex-direction: column;">' +
                                '<div class="name-rating" style="display: flex; align-items: center;">' +
                                    '<h4 style="font-weight: bold; color: black; margin: 0;">' + SS.esc(data.review.name) + '</h4>' +
                                '</div>' +
                                '<div class="review-body" style="margin-top: 5px;"><p>' + SS.esc(data.review.message) + '</p></div>' +
                            '</div>' +
                        '</div>' +
                    '</div>'
                );
            })
            .catch(function (error) { console.error('Error:', error); });
    });

    var stars = document.getElementsByClassName('star');

    function resetStars() {
        for (var i = 0; i < stars.length; i++) {
            stars[i].style.color = '#ccc';
        }
    }

    // Called by the stars' onclick in the review form
    window.gfg = function (n) {
        resetStars();
        for (var i = 0; i < n; i++) {
            stars[i].style.color = 'red';
        }
    };
});
