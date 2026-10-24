// Shop page: searching, sorting and paging reload only the product list.
// Categories are links to their own pages (/shop/{category}), so they load normally.
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

    $('#search-form').on('submit', function (e) {
        e.preventDefault();
        loadProducts($(this).attr('action'), $(this).serialize(), {resetSort: true});
    });

    // Phones: the dropdown holds the category page addresses
    $('#category-phone').on('change', function () {
        window.location.href = $(this).val();
    });

    $('#sort-by-price').on('change', function () {
        $('#sort-form').trigger('submit');
    });

    // The sort form posts to the page it is on, so the category stays chosen
    $('#sort-form').on('submit', function (e) {
        e.preventDefault();
        loadProducts($(this).attr('action'), $(this).serialize());
    });

    $(document).on('click', '#pagination-container .pagination a', function (e) {
        e.preventDefault();
        loadProducts($(this).attr('href'), null, {pagination: '#pagination-container', scroll: true});
    });
});
