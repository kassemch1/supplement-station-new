
<!doctype html>
<html lang="zxx">
<head>

    <!--========= Required meta tags =========-->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

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
@include('partials/navBar')
    <!-- header end -->

    <!-- sidebar-info end -->

    <div class="body-overlay"></div>

    <!-- main area start  -->
    <main>
        <!-- breadcrumb start -->
        <section class="breadcrumb position-bottom bg_img" data-background="{{ asset('assets/img/bg/page_title.png')}}">
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
                                    <form id="search-form" class="widget__search" method="GET">
                                        <input type="text" name="search" placeholder="Search..." id="search-input">
                                        <button type="submit"><i class="far fa-search"></i></button>
                                    </form>
                                </div>
                            </div>


                            <div class="widget mt-40">
                                <h2 class="widget__title">
                                    <span>Product Categories</span>
                                </h2>
                                <div class="widget__inner">
                                    <ul class="widget__category list-unstyled">
                                        @foreach($categories as $category)
                                            <li>
                                                <!-- Use data-category instead of href -->
                                                <a href="#" data-category="{{ $category->name }}">
                                                    {{ $category->name }}
                                                    <span></span>
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>


                            <div class="widget mt-40">
                                <h2 class="widget__title">Top Rated Products</h2>
                                <div class="widget__inner">
                                    <ul class="widget-product">
                                        <li class="widget-product__item">
                                            <div class="thumb">
                                                <a href="shop-single.html"><img src="{{ asset('assets/img/shop/prd_01.jpg')}}" alt=""></a>
                                            </div>
                                            <div class="content">
                                                <h3><a href="shop-single.html">iso whey powder</a></h3>
                                                <span class="price">$129.00</span>
                                                <ul class="rating ul_li">
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="widget-product__item">
                                            <div class="thumb">
                                                <a href="shop-single.html"><img src="{{ asset('assets/img/shop/prd_02.jpg')}}" alt=""></a>
                                            </div>
                                            <div class="content">
                                                <h3><a href="shop-single.html">xplode powder</a></h3>
                                                <span class="price">$129.00</span>
                                                <ul class="rating ul_li">
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="widget-product__item">
                                            <div class="thumb">
                                                <a href="shop-single.html"><img src="{{ asset('assets/img/shop/prd_03.jpg')}}" alt=""></a>
                                            </div>
                                            <div class="content">
                                                <h3><a href="shop-single.html">strawberry protine</a></h3>
                                                <span class="price">$129.00</span>
                                                <ul class="rating ul_li">
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                </ul>
                                            </div>
                                        </li>
                                        <li class="widget-product__item">
                                            <div class="thumb">
                                                <a href="shop-single.html"><img src="{{ asset('assets/img/shop/prd_04.jpg')}}" alt=""></a>
                                            </div>
                                            <div class="content">
                                                <h3><a href="shop-single.html">nutraone protine</a></h3>
                                                <span class="price">$129.00</span>
                                                <ul class="rating ul_li">
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                    <li><i class="fas fa-star"></i></li>
                                                </ul>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
{{--                            <div class="widget widget_price_filter mt-40">--}}
{{--                                <h2 class="widget__title">Filter By Price</h2>--}}
{{--                                <div class="widget__inner">--}}
{{--                                    <div class="filter-price">--}}
{{--                                        <form>--}}
{{--                                            <div id="slider-range"></div>--}}
{{--                                            <p>Price : <input type="text" id="amount"></p>--}}
{{--                                            <button>filter</button>--}}
{{--                                        </form>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                            <div class="widget mt-40">--}}
{{--                                <h2 class="widget__title">--}}
{{--                                    <span>Tags</span>--}}
{{--                                </h2>--}}
{{--                                <div class="widget__inner">--}}
{{--                                    <div class="tagcloud">--}}
{{--                                        <a href="#!">energy</a>--}}
{{--                                        <a href="#!">fitness</a>--}}
{{--                                        <a href="#!">healthy</a>--}}
{{--                                        <a href="#!">powders</a>--}}
{{--                                        <a href="#!">nutrition</a>--}}
{{--                                        <a href="#!">snacks</a>--}}
{{--                                        <a href="#!">wellness</a>--}}
{{--                                        <a href="#!">powders</a>--}}
{{--                                    </div>--}}
{{--                                </div>--}}
{{--                            </div>--}}
                        </div>
                    </div>
                    <div class="col-lg-9 mt-60">
                        <div class="woocommerce-content-wrap">
                            <div class="woocommerce-toolbar-top ul_li_between">
                                <p class="woocommerce-result-count">Showing 1–12 of 70 results</p>
                                <div class="woocommerce-toolbar-top-right ul_li">
                                    <form class="woocommerce-ordering" method="get">
                                        <select name="orderby" class="orderby" id="sort-by-price">
                                            <option value="menu_order" selected="selected">Default sorting</option>
                                            <option value="price">Sort by price: low to high</option>
                                            <option value="price-desc">Sort by price: high to low</option>
                                        </select>

                                        <input type="hidden" name="post_type" value="product">
                                    </form>
                                </div>
                            </div>
                            <div class="woocommerce-content-inner">
                                <div class="products" id="product-list">
                                    <div class="row">
                                        @foreach($product as $item)
                                            <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                                                <div class="product product-item text-center" style="position: relative; overflow: hidden;">
                                                    @if($item->discount > 0)
                                                        <div class="ribbon" style="
                                                            width: 150px;
                                                            height: 150px;
                                                            overflow: hidden;
                                                            position: absolute;
                                                            top: -10px;
                                                            right: -10px;
                                                            z-index: 2;
                                                        ">
                                                            <span style="
                                                                position: absolute;
                                                                display: block;
                                                                width: 225px;
                                                                padding: 15px 0;
                                                                background-color: red;
                                                                color: white;
                                                                text-transform: uppercase;
                                                                font-weight: bold;
                                                                text-align: center;
                                                                transform: rotate(45deg);
                                                                top: 30px;
                                                                right: -65px;
                                                            ">On Sale</span>
                                                        </div>
                                                    @endif
                                                    <a href="{{ route('products.show', $item->id) }}">
                                                        <div class="xb-item--img">
                                                            @if($item->images->isNotEmpty())
                                                                <img src="{{asset($item->images->first()->url)}}" alt="img" style="width: 155px; height: 170px; object-fit: cover;">
                                                            @else
                                                                No image available
                                                            @endif
                                                        </div>
                                                    </a>
                                                    <div class="xb-item--holder">
                                                        <h3 class="xb-item--title">
                                                            <a href="shop-single.html">{{ $item->name }}</a>
                                                        </h3>
                                                        <div class="xb-item--rating-inner ul_li_center">
                                                            <ul class="xb-item--rating ul_li">
                                                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                                                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                                                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                                                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                                                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                                                            </ul>
                                                            <span>(36)</span>
                                                        </div>
                                                    </div>
                                                    <div class="xb-item--action ul_li mt-20">
                                                        <span class="xb-item--price">
                                                            @if($item->discount > 0)
                                                                <span style="text-decoration: line-through; color: gray;">${{ number_format($item->price, 2) }}</span>
                                                                <span style="color: red;">${{ number_format($item->price - ($item->price * $item->discount / 100), 2) }}</span>
                                                            @else
                                                                ${{ number_format($item->price, 2) }}
                                                            @endif
                                                        </span>
                                                        <a href="shop-single.html">
                                                            <span class="xb-item--cart-icon"><img src="{{ asset('assets/img/icon/bag.svg') }}" alt="Cart"></span>
                                                            <span class="xb-item--cart">add to cart</span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>



                                </div>

                                <div class="pagination_wrap pt-25">
                                    <ul>
                                        @if ($product->currentPage() > 1)
                                            <li><a href="#" data-page="{{ $product->currentPage() - 1 }}"><i class="fal fa-angle-double-left"></i></a></li>
                                        @endif

                                        @for ($i = 1; $i <= $product->lastPage(); $i++)
                                            <li><a href="#" class="{{ $product->currentPage() == $i ? 'current_page' : '' }}" data-page="{{ $i }}">{{ $i }}</a></li>
                                        @endfor

                                        @if ($product->currentPage() < $product->lastPage())
                                            <li><a href="#" data-page="{{ $product->currentPage() + 1 }}"><i class="fal fa-angle-double-right"></i></a></li>
                                        @endif
                                    </ul>
                                </div>



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


</body>


</html>
<div class="header-shop-cart">
    <a href="javascript:void(0);"><img src="assets/img/icon/bag.svg" alt=""><span class="mini-cart-count">2</span></a>
    <div class="header-mini-cart">
        <!-- Cart items will be dynamically inserted here -->
    </div>
</div>
<script>
    function fetchCart() {
        $.ajax({
            url: '{{ route('api.cart.get') }}',
            method: 'GET',
            success: function(response) {
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
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    // Fetch cart items on page load
    $(document).ready(function() {
        $('.header-shop-cart a').on('click', function() {
            $('.header-mini-cart').toggle(); // Toggle visibility on click
        });
        fetchCart(); // Fetch cart items
    });

    // Remove item from cart
    $(document).on('click', '.remove', function(event) {
        event.preventDefault(); // Prevent the default link behavior

        const productId = $(this).data('product_id');

        $.ajax({
            url: '{{ route('cart.remove') }}',
            method: 'POST',
            data: {
                product_id: productId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                alert(response.message);
                fetchCart(); // Refresh the cart items
            },
            error: function(xhr, status, error) {
                alert('Failed to remove item from cart.');
            }
        });
    });
</script>







<script>
    document.addEventListener('DOMContentLoaded', function() {
        var products = @json($product);

        document.querySelectorAll('.pagination_wrap a[data-page]').forEach(function(pageLink) {
        pageLink.addEventListener('click', function(event) {
            event.preventDefault();

            const page = this.getAttribute('data-page');
            const searchQuery = document.getElementById('search-input') ? document.getElementById('search-input').value.trim() : '';
            const sortOrder = document.getElementById('sort-by-price') ? document.getElementById('sort-by-price').value : '';
            const category = document.querySelector('.widget__category a.active') ? document.querySelector('.widget__category a.active').getAttribute('data-category') : '';

            const url = `/shop?page=${page}&search=${encodeURIComponent(searchQuery)}&orderby=${encodeURIComponent(sortOrder)}&category=${encodeURIComponent(category)}`;

            fetchProducts(url, page); // Pass the current page
        });
    });

    // Function to fetch products
    function fetchProducts(url, currentPage) {
        fetch(url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            const productList = document.getElementById('product-list');
            let productHtml = '';

            if (data.product.length > 0) {
                data.product.forEach(function(product) {
                    console.log(product.images)
                    productHtml += `
                        <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                            <div class="product product-item text-center">
                                <div class="xb-item--img">
                                    <a href="shop-single.html">
                                        <img src="${product.images[0].url}" alt="img" style="width: 155px; height: 170px; object-fit: cover;">
                                    </a>
                                </div>
                                <div class="xb-item--holder">
                                    <h3 class="xb-item--title">
                                        <a href="shop-single.html">${product.name}</a>
                                    </h3>
                                    <div class="xb-item--rating-inner ul_li_center">
                                        <ul class="xb-item--rating ul_li">
                                            <li><img src="assets/img/icon/star.png" alt="Star"></li>
                                            <li><img src="assets/img/icon/star.png" alt="Star"></li>
                                            <li><img src="assets/img/icon/star.png" alt="Star"></li>
                                            <li><img src="assets/img/icon/star.png" alt="Star"></li>
                                            <li><img src="assets/img/icon/star.png" alt="Star"></li>
                                        </ul>
                                        <span>(36)</span>
                                    </div>
                                </div>
                                <div class="xb-item--action ul_li mt-20">
                                    <span class="xb-item--price">$${product.price}</span>
                                    <a href="shop-single.html">
                                        <span class="xb-item--cart-icon"><img src="assets/img/icon/bag.svg" alt="Cart"></span>
                                        <span class="xb-item--cart">add to cart</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                productHtml = '<p>No products found.</p>';
            }

            productList.innerHTML = `<div class="row">${productHtml}</div>`;

            // Update pagination links
            updatePaginationLinks(currentPage);
        })
        .catch(error => console.error('Error fetching products:', error));
    }

    function updatePaginationLinks(currentPage) {
        document.querySelectorAll('.pagination_wrap a[data-page]').forEach(function(pageLink) {
            if (pageLink.getAttribute('data-page') == currentPage) {
                pageLink.classList.add('current_page');
            } else {
                pageLink.classList.remove('current_page');
            }
        });
    }



        // Handle search form submission with AJAX
        document.getElementById('search-form').addEventListener('submit', function(event) {
            event.preventDefault();
            const searchQuery = document.getElementById('search-input').value.trim();
            const url = `/shop?search=${encodeURIComponent(searchQuery)}`;

            fetchProducts(url);
        });

        // Handle category clicks
        document.querySelectorAll('.widget__category a').forEach(function(categoryLink) {
            categoryLink.addEventListener('click', function(event) {
                event.preventDefault();

                document.querySelectorAll('.widget__category a').forEach(link => link.classList.remove('active'));
                this.classList.add('active');

                const selectedCategory = this.getAttribute('data-category').trim();
                const url = `/shop?category=${encodeURIComponent(selectedCategory)}`;

                fetchProducts(url);
            });
        });

        // Handle sorting
        document.getElementById('sort-by-price').addEventListener('change', function(event) {
            const sortOrder = this.value;
            const searchQuery = document.getElementById('search-input') ? document.getElementById('search-input').value.trim() : '';
            const url = `/shop?orderby=${encodeURIComponent(sortOrder)}&search=${encodeURIComponent(searchQuery)}`;

            fetchProducts(url);
        });
    });
    </script>

<style>
/* Styling for the sale label */
.sale-label {
    background-color: red; /* Background color for the label */
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

    </style>
<div class="header-shop-cart">
    <a href="javascript:void(0);"><img src="assets/img/icon/bag.svg" alt=""><span class="mini-cart-count">0</span></a>
    <div class="header-mini-cart">
        <!-- Cart items will be dynamically inserted here -->
    </div>
</div>
<script>
    function fetchCart() {
        $.ajax({
            url: '{{ route('api.cart.get') }}',
            method: 'GET',
            success: function(response) {
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
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }

    // Fetch cart items on page load
    $(document).ready(function() {
        $('.header-shop-cart a').on('click', function() {
            $('.header-mini-cart').toggle(); // Toggle visibility on click
        });
        fetchCart(); // Fetch cart items
    });
// Remove item from mini cart
$(document).on('click', '.remove', function(event) {
    event.preventDefault(); // Prevent the default link behavior

    const productId = $(this).data('product_id');

    $.ajax({
        url: '{{ route('cart.remove') }}',
        method: 'POST',
        data: {
            product_id: productId,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                alert(response.message);
                fetchCart(); // Refresh the cart items
            } else {
                alert('Failed to remove item from cart.'); // Handle unexpected response
            }
        },
        error: function(xhr, status, error) {
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

