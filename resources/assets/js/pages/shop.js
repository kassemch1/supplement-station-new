// Shop page: searching, category filters, sorting and paging reload only the product list
$(function () {
    'use strict';

    function loadProducts(url, data, options) {
        options = options || {};
        $.get(url, data)
            .done(function (response) {
                if (options.resetSort) {
                    $('#sort-by-price').val('sort');
                }
                $('#product-list').html(response.productList || '<p>No products found.</p>');
                $(options.pagination || '#pagination').html(response.paginatee || '');
                $('#products_indexing').html(response.productsIndexing || '');
                if (options.scroll) {
                    $('html, body').animate({scrollTop: $('#product-list').offset().top}, 500);
                }
            })
            .fail(function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert('An error occurred while processing the request.');
            });
    }

    $('#search-form, .category-form, .category-form-phone').on('submit', function (e) {
        e.preventDefault();
        loadProducts($(this).attr('action'), $(this).serialize(), {resetSort: true});
    });

    $('#category-phone').on('change', function () {
        $('.category-form-phone').trigger('submit');
    });

    $('.category-button').on('click', function () {
        $('.category-button').removeClass('active');
        $(this).addClass('active');
    });

    $('#sort-by-price').on('change', function () {
        $('#sort-form').trigger('submit');
    });

    // Sorting keeps the category that is chosen: the dropdown on phones, the active button otherwise
    // (both are on the page; the screen width decides which one is visible)
    $('#sort-form').on('submit', function (e) {
        e.preventDefault();
        var $phone = $('#category-phone');
        var category;
        if ($phone.is(':visible')) {
            category = $phone.val();
        } else {
            var $active = $('.category-form button.active');
            category = ($active.length ? $active.closest('form') : $('.category-form').first()).find('input[name="category"]').val();
        }
        $('#category-input').val(category);
        loadProducts($(this).attr('action'), $(this).serialize());
    });

    $(document).on('click', '#pagination-container .pagination a', function (e) {
        e.preventDefault();
        loadProducts($(this).attr('href'), null, {pagination: '#pagination-container', scroll: true});
    });
});
