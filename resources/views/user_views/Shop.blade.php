<!doctype html>
<html lang="zxx">
<head>

    <!--========= Required meta tags =========-->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Shop</title>

    <link rel="shortcut icon" href="{{ asset('assets/img/favicon.png') }}" type="images/x-icon"/>

    <!-- css include -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/swiper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">


</head>

<body>

<!-- backtotop - start -->
<div class="xb-backtotop">
    <a href="#" class="scroll">
        <i class="far fa-arrow-up"></i>
    </a>
</div>
<!-- backtotop - end -->
@include('partials/navBar',['categories'=>$categories])
<!-- header end -->

<!-- sidebar-info end -->

<div class="body-overlay"></div>

<!-- main area start  -->
<main>
    <!-- breadcrumb start -->
    <section class="breadcrumb position-bottom bg_img" data-background="{{ asset('assets/img/bg/page_title2.jpg')}}">
        <div class="container">
            <div class="breadcrumb__content text-center">
                <h2 class="breadcrumb__title">Shop</h2>
                <ul class="breadcrumb__list clearfix">
                    <li class="breadcrumb-item"><a href="index.html">Supplement Station</a></li>
                    <li class="breadcrumb-item">Shop</li>
                </ul>
            </div>
        </div>
    </section>
    <!-- breadcrumb end -->

    <!-- shop start -->
    <section class="shop pt-115 pb-385">
        <div class="container">
            <div class="row mt-none-60">
                <div class="col-lg-3 mt-60">
                    <div class="shop-sidebar sidebar-area mt-none-40">

                        <div class="widget mt-40">
                            <h2 class="widget__title">Search</h2>
                            <div class="widget__inner">
                                <form id="search-form" class="widget__search" method="GET" action="{{route('shop')}}">
                                    @csrf
                                    <input type="text" name="shop-search" placeholder="Search..." id="search-input">
                                    <button type="submit"><i class="far fa-search"></i></button>
                                </form>
                            </div>
                        </div>


                        <div class="widget mt-40">
                            <h2 class="widget__title">
                                <span>Product Categories</span>
                            </h2>
                            @if($agent->isDesktop() || $agent->isTablet())
                                <div class="widget__inner"
                                     style="max-height: 300px; overflow-y: auto; padding-right: 10px; box-sizing: border-box;">
                                    <ul class="widget__category list-unstyled">
                                        <li>
                                                <form action="{{route('shop')}}" method="GET" class="category-form">
                                                    @csrf
                                                    <input type="hidden" name="category" value="show-all">
                                                    <button type="submit" class="btn btn-link" style="text-decoration: none;color: inherit">
                                                        Show All
                                                    </button>

                                                </form>
                                        </li>
                                        @foreach($categories as $category)
                                            <li>
                                                <form action="{{route('shop')}}" method="GET" class="category-form">
                                                    @csrf
                                                    <input type="hidden" name="category" value="{{ $category->name }}">
                                                    <button type="submit" class="btn btn-link" style="text-decoration: none;color: inherit">
                                                        {{ $category->name }}
                                                    </button>

                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @elseif($agent->isMobile())
                                <div class="widget__inner"
                                     style="max-height: 300px; overflow-y: auto; padding-right: 10px; box-sizing: border-box;">
                                    <ul class="widget__category list-unstyled">
                                            <li>
                                                <form action="{{route('shop')}}" method="GET" class="category-form-phone">
                                                    @csrf
                                                    <select name="category-phone" id="category-phone">
                                                        <option value="all">Show All</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{$category->name}}">{{$category->name}}</option>
                                                        @endforeach
                                                    </select>

                                                </form>
                                            </li>
                                    </ul>
                                </div>
                            @endif


                        </div>
                        @if(!$agent->isMobile())
                        <!--Start Of the Offers Section -->
                        @if($offersProducts)
                            <div class="widget mt-40">
                                <h2 class="widget__title">Our Best Offers</h2>
                                <div class="widget__inner">
                                    <ul class="widget-product">
                                        @foreach($offersProducts as $offer)
                                            <li class="widget-product__item">
                                                <div class="thumb">
                                                    <a href="{{ route('products.show', ['id' => $offer->id]) }}">
                                                        @if($offer->images->isNotEmpty())
                                                            <img src="{{ asset($offer->images->first()->url) }}" alt="">
                                                        @else
                                                            No image available
                                                        @endif
                                                    </a>
                                                </div>
                                                <div class="content">
                                                    <h3>
                                                        <a href="{{ route('products.show', ['id' => $offer->id]) }}">{{ $offer->name }}</a>
                                                    </h3>
                                                    <span class="price">
                                                        @if($offer->discount > 0)
                                                            <span
                                                                style="text-decoration: line-through; color: gray;">${{ number_format($offer->price, 2) }}</span>
                                                            <span
                                                                style="color: #A02334;">${{ number_format($offer->price - ($offer->price * $offer->discount / 100), 2) }}</span>
                                                        @else
                                                            ${{ number_format($offer->price, 2) }}
                                                        @endif
                                                    </span>

                                                </div>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif
                        @endif

                        <!--End Of the Offers Section -->


                    </div>
                </div>
                <div class="col-lg-9 mt-60">
                    <div class="woocommerce-content-wrap">
                        <div class="woocommerce-toolbar-top ul_li_between">
                            <p class="woocommerce-result-count">Showing {{$startIndex}}–{{$endIndex}} of {{$totalProducts}} results</p>
                            <div class="woocommerce-toolbar-top-right ul_li">
                                <form id="sort-form" class="woocommerce-ordering" method="get" action="{{route('shop')}}">
                                    @csrf
                                    <select name="orderby" class="orderby" id="sort-by-price">
                                        <option value="default" selected="selected">Apply sorting</option>
                                        <option value="default">Default</option>
                                        <option value="low-to-high">Sort by price: low to high</option>
                                        <option value="high-to-low">Sort by price: high to low</option>
                                    </select>
                                    <input type="hidden" name="post_type" value="product">
                                </form>
                            </div>
                        </div>
                        <div class="woocommerce-content-inner">
                            <div class="products" id="product-list">
                                @include('partials/product_list',['product'=>$product,'agent'=>$agent])
                            </div>

                            <div id="pagination">
                                @include('partials/product_pagination',['product'=>$product])
                            </div>


                        </div>
                    </div>
                </div>

                <div class="row mt-none-60">
                    <div class="col-lg-3 mt-60">
                        <div class="shop-sidebar sidebar-area mt-none-40">
                @if($agent->isMobile())
                    <!--Start Of the Offers Section -->
                    @if($offersProducts)
                        <div class="widget mt-40">
                            <h2 class="widget__title">Our Best Offers</h2>
                            <div class="widget__inner">
                                <ul class="widget-product">
                                    @foreach($offersProducts as $offer)
                                        <li class="widget-product__item">
                                            <div class="thumb">
                                                <a href="{{ route('products.show', ['id' => $offer->id]) }}">
                                                    @if($offer->images->isNotEmpty())
                                                        <img src="{{ asset($offer->images->first()->url) }}" alt="">
                                                    @else
                                                        No image available
                                                    @endif
                                                </a>
                                            </div>
                                            <div class="content">
                                                <h3>
                                                    <a href="{{ route('products.show', ['id' => $offer->id]) }}">{{ $offer->name }}</a>
                                                </h3>
                                                <span class="price">
                                                        @if($offer->discount > 0)
                                                        <span
                                                            style="text-decoration: line-through; color: gray;">${{ number_format($offer->price, 2) }}</span>
                                                        <span
                                                            style="color: #A02334;">${{ number_format($offer->price - ($offer->price * $offer->discount / 100), 2) }}</span>
                                                    @else
                                                        ${{ number_format($offer->price, 2) }}
                                                    @endif
                                                    </span>

                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
    <!-- shop end -->

</main>
<!-- main area end  -->

<!-- footer strt -->
@include('partials/footer')
<!-- footer end -->


</div>

<!-- jquery include -->
<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/js/swiper.min.js') }}"></script>
<script src="{{ asset('assets/js/wow.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ asset('assets/js/touchspin.js') }}"></script>
<script src="{{ asset('assets/js/jquery-ui.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.inview.min.js') }}"></script>
<script src="{{ asset('assets/js/jquery.easing.js') }}"></script>
<script src="{{ asset('assets/js/scrollspy.js') }}"></script>
<script src="{{ asset('assets/js/main.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#search-form').submit(function (e) {
            e.preventDefault(); // Prevent default form submission

            // Serialize form data
            var formData = $(this).serialize(); // Convert form data to a query string

            // Send AJAX request
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'GET',
                url: $(this).attr('action'), // Use form's action attribute as URL
                data: formData,
                success: function (response) {
                    // Ensure response keys match those sent from the server
                    if (response.productList) {
                        $('#product-list').empty().html(response.productList);
                    } else {
                        $('#product-list').html('<p>No products found.</p>');
                    }

                    if (response.paginatee) { // Corrected the key name
                        $('#pagination').empty().html(response.paginatee);
                    } else {
                        $('#pagination').html('');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('An error occurred while processing the request.');
                }
            });
        });
    });

    // Handle sort form change
    $('#sort-by-price').change(function () {
        $('#sort-form').submit(); // Submit the form via AJAX
    });

    // Handle sort form submission via AJAX
    $('#sort-form').submit(function (e) {
        e.preventDefault(); // Prevent default form submission

        var formData = $(this).serialize(); // Convert form data to a query string

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: $(this).attr('action'), // Use form's action attribute as URL
            data: formData,
            success: function (response) {
                if (response.productList) {
                    $('#product-list').empty().html(response.productList);
                } else {
                    $('#product-list').html('<p>No products found.</p>');
                }

                if (response.paginatee) {
                    $('#pagination').empty().html(response.paginatee);
                } else {
                    $('#pagination').html('');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert('An error occurred while processing the request.');
            }
        });
    });

    // Handle category form submission via AJAX
    $('.category-form').submit(function (e) {
        e.preventDefault(); // Prevent default form submission

        var formData = $(this).serialize(); // Convert form data to a query string

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: $(this).attr('action'), // Use form's action attribute as URL
            data: formData,
            success: function (response) {
                if (response.productList) {
                    $('#product-list').empty().html(response.productList);
                } else {
                    $('#product-list').html('<p>No products found.</p>');
                }

                if (response.paginatee) {
                    $('#pagination').empty().html(response.paginatee);
                } else {
                    $('#pagination').html('');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert('An error occurred while processing the request.');
            }
        });
    });


    // Handle category form change
    $('#category-phone').change(function () {
        $('.category-form-phone').submit(); // Submit the form via AJAX
    });

    // Handle category form submission via AJAX
    $('.category-form-phone').submit(function (e) {
        e.preventDefault(); // Prevent default form submission

        var formData = $(this).serialize(); // Convert form data to a query string

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'GET',
            url: $(this).attr('action'), // Use form's action attribute as URL
            data: formData,
            success: function (response) {
                if (response.productList) {
                    $('#product-list').empty().html(response.productList);
                } else {
                    $('#product-list').html('<p>No products found.</p>');
                }

                if (response.paginatee) {
                    $('#pagination').empty().html(response.paginatee);
                } else {
                    $('#pagination').html('');
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX Error:', status, error);
                alert('An error occurred while processing the request.');
            }
        });
    });


</script>

<script>
    $(document).ready(function() {
        // Function to handle pagination clicks
        $(document).on('click', '#pagination-container .pagination a', function(e) {
            e.preventDefault(); // Prevent the default link behavior

            var url = $(this).attr('href'); // Get the URL from the link
            fetchPage(url);
        });

        // Function to fetch page content via AJAX
        function fetchPage(url) {
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'GET',
                url: url,
                success: function(response) {
                    // Update the product list and pagination links
                    if (response.productList) {
                        $('#product-list').empty().html(response.productList);
                    } else {
                        $('#product-list').html('<p>No products found.</p>');
                    }

                    if (response.paginatee) {
                        $('#pagination-container').empty().html(response.paginatee);
                    } else {
                        $('#pagination-container').html('');
                    }

                    // Scroll to the top of the products
                    $('html, body').animate({
                        scrollTop: $('#product-list').offset().top
                    }, 500);
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    alert('An error occurred while processing the request.');
                }
            });
        }
    });

</script>
</body>
<style>
    /* Styling for the sale label */
    .sale-label {
        background-color: #A02334; /* Background color for the label */
        color: white; /* Text color */
        padding: 5px 10px; /* Padding around the text */
        font-weight: bold; /* Bold text */
        position: absolute; /* Absolute positioning */
        top: 10px; /* Position it at the top */
        left: 10px; /* Adjust as needed */
        z-index: 10; /* Ensure it appears above the image */
        border-radius: 3px; /* Rounded corners */
        text-transform: uppercase; /* Uppercase text */
        font-size: 14px; /* Font size */
    }

    .xb-item--img {
        position: relative; /* Needed for absolute positioning of the label */
        display: inline-block; /* Ensure the container fits around the image */
    }

    .xb-item--cart-btn.disabled {
        pointer-events: none;
        opacity: 0.5;
    }

    .no-stock img {
        pointer-events: none;
    }

    .no-stock-title {
        color: gray;
        cursor: default;
    }

    /* Customize the overall pagination container */
    .pagination {
        display: flex;
        justify-content: center;
        padding: 0;
        margin: 0;
    }

    /* Style for pagination items */
    .pagination .page-item {
        margin: 0 2px; /* Spacing between buttons */
    }

    /* Style for pagination links */
    .pagination .page-link {
        color: #ffffff; /* Text color */
        background-color: #A02334; /* Background color */
        border: 1px solid #A02334; /* Border color */
    }

    /* Style for active pagination link */
    .pagination .page-item.active .page-link {
        background-color: #fff; /* Active background color */
        color: #A02334;
        border-color: #A02334; /* Active border color */
    }

    /* Style for disabled pagination link */
    .pagination .page-item.disabled .page-link {
        color: #6c757d; /* Disabled text color */
        background-color: #e9ecef; /* Disabled background color */
        border-color: #e9ecef; /* Disabled border color */
    }

    /* Hover effect for pagination links */
    .pagination .page-link:hover {
        background-color: #fff; /* Hover background color */
        border-color: #fff; /* Hover border color */
        color: #A02334;
    }


</style>

</html>


<!--minicart-->
<script>
    function fetchCart() {
        $.ajax({
            url: '{{ route('api.cart.get') }}',
            method: 'GET',
            success: function (response) {
                console.log("response", response.items); // Check response structure

                if (!response.items || !Array.isArray(response.items)) {
                    console.error("Invalid response structure");
                    return;
                }

                $('.header-mini-cart').empty(); // Clear previous items
                let total = 0;

                if (response.items.length === 0) {
                    $('.header-mini-cart').append('<p>Your cart is empty.</p>');
                } else {
                    response.items.forEach(item => {
                        if (!item.product || !item.product.price || !item.product.name) {
                            console.error("Invalid item structure");
                            return;
                        }

                        const discount = item.product.discount || 0; // Get discount percentage
                        const price = item.product.price;
                        const discountedPrice = discount ? price * (1 - (discount / 100)) : price; // Apply discount
                        const itemTotal = discountedPrice * item.quantity; // Calculate total for this item

                        $('.header-mini-cart').append(`
                            <div class="woocommerce-mini-cart-item d-flex align-items-center" style="padding: 10px;">
                                <div class="mini-cart-img" style="margin-right: 10px;">
                                    <img src="${item.product_image || 'path/to/default/image.jpg'}" alt="${item.product.name}" style="width: 50px; height: 50px; object-fit: cover;">
                                </div>
                                <div class="mini-cart-content" style="flex-grow: 1;">
                                    <h4 class="product-title" style="margin: 0; font-size: 14px;">
                                        <a href="shop-details.html" style="text-decoration: none; color: #000;">${item.product.name}</a>
                                    </h4>
                                    <div class="mini-cart-price" style="margin-top: 5px;">
                                        ${item.quantity} ×
                                        <span class="woocommerce-Price-amount amount" style="color: red;">$${discountedPrice.toFixed(2)}</span>

                                    </div>
                                </div>
                                <div class="remove-button" style="margin-left: auto;">
                                    <a href="#" class="remove remove_from_cart_button" data-product_id="${item.product.id}" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: lightgrey; text-align: center; line-height: 20px; color: red; font-size: 14px;">×</a>
                                </div>
                            </div>
                        `);

                        total += itemTotal; // Sum the total amount
                    });

                    $('.header-mini-cart').append(`
                        <p class="woocommerce-mini-cart__total">
                            <strong>Subtotal:</strong>
                            <span class="woocommerce-Price-amount">$${total.toFixed(2)}</span>
                        </p>
                        <p class="checkout-link">
                            <a href="/viewCart" class="button wc-forward">View cart</a>
                            <a href="/Checkout" class="button checkout wc-forward">Checkout</a>
                        </p>
                    `);
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    // Fetch cart items on page load
    $(document).ready(function () {
        $('.header-shop-cart a').on('click', function () {
            $('.header-mini-cart').toggle(); // Toggle visibility on click
        });
        fetchCart(); // Fetch cart items
    });

    // Remove item from cart
    $(document).on('click', '.remove', function (event) {
        event.preventDefault(); // Prevent the default link behavior

        const productId = $(this).data('product_id');

        $.ajax({
            url: '{{ route('cart.remove') }}',
            method: 'POST',
            data: {
                product_id: productId,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                alert(response.message);
                fetchCart(); // Refresh the cart items
            },
            error: function (xhr, status, error) {
                alert('Failed to remove item from cart.');
            }
        });
    });
</script>



<script>
    function fetchCart() {
        $.ajax({
            url: '{{ route('api.cart.get') }}',
            method: 'GET',
            success: function (response) {
                console.log("response", response.items); // Check response structure

                if (!response.items || !Array.isArray(response.items)) {
                    console.error("Invalid response structure");
                    return;
                }

                $('.header-mini-cart').empty(); // Clear previous items
                let total = 0;
                let itemCount = 0; // Initialize item count

                if (response.items.length === 0) {
                    $('.header-mini-cart').append('<p>Your cart is empty.</p>');
                } else {
                    response.items.forEach(item => {
                        if (!item.product || !item.product.price || !item.product.name) {
                            console.error("Invalid item structure");
                            return;
                        }

                        const discount = item.product.discount || 0; // Get discount percentage
                        const price = item.product.price;
                        const discountedPrice = discount ? price * (1 - (discount / 100)) : price; // Apply discount
                        const itemTotal = discountedPrice * item.quantity; // Calculate total for this item
                        itemCount += item.quantity; // Sum the quantities for the cart count

                        $('.header-mini-cart').append(`
                            <div class="woocommerce-mini-cart-item d-flex align-items-center" style="padding: 10px;">
                                <div class="mini-cart-img" style="margin-right: 10px;">
                                    <img src="${item.product_image || 'path/to/default/image.jpg'}" alt="${item.product.name}" style="width: 50px; height: 50px; object-fit: cover;">
                                </div>
                                <div class="mini-cart-content" style="flex-grow: 1;">
                                    <h4 class="product-title" style="margin: 0; font-size: 14px;">
                                        <a href="shop-details.html" style="text-decoration: none; color: #000;">${item.product.name}</a>
                                    </h4>
                                    <div class="mini-cart-price" style="margin-top: 5px;">
                                        ${item.quantity} ×
                                        <span class="woocommerce-Price-amount amount" style="color: red;">$${discountedPrice.toFixed(2)}</span>

                                    </div>
                                </div>
                                <div class="remove-button" style="margin-left: auto;">
                                    <a href="#" class="remove remove_from_cart_button" data-product_id="${item.product.id}" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: lightgrey; text-align: center; line-height: 20px; color: red; font-size: 14px;">×</a>
                                </div>
                            </div>
                        `);

                        total += itemTotal; // Sum the total amount
                    });

                    $('.header-mini-cart').append(`
                        <p class="woocommerce-mini-cart__total">
                            <strong>Subtotal:</strong>
                            <span class="woocommerce-Price-amount">$${total.toFixed(2)}</span>
                        </p>
                        <p class="checkout-link">
                            <a href="/viewCart" class="button wc-forward">View cart</a>
                            <a href="/Checkout" class="button checkout wc-forward">Checkout</a>
                        </p>
                    `);
                    $('.mini-cart-count').text(itemCount); // Update the cart count in the header
                }
            },
            error: function (xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    // Fetch cart items on page load
    $(document).ready(function () {
        $('.header-shop-cart a').on('click', function () {
            $('.header-mini-cart').toggle(); // Toggle visibility on click
        });
        fetchCart(); // Fetch cart items
    });
    // Remove item from mini cart
    $(document).on('click', '.remove', function (event) {
        event.preventDefault(); // Prevent the default link behavior

        const productId = $(this).data('product_id');

        $.ajax({
            url: '{{ route('cart.remove') }}',
            method: 'POST',
            data: {
                product_id: productId,
                _token: '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    fetchCart(); // Refresh the cart items
                } else {
                    alert('Failed to remove item from cart.'); // Handle unexpected response
                }
            },
            error: function (xhr, status, error) {
                // Check if the response contains a JSON message
                try {
                    const errResponse = JSON.parse(xhr.responseText);
                    alert(errResponse.message || 'Failed to remove item from cart.');
                } catch (e) {
                    alert('Failed to remove item from cart.');
                }
            }
        });
    });


</script>


