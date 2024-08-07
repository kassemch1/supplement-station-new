
<!-- preloader start -->
<div id="xb-loadding" class="xb-loadding-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; z-index: 9999; background-color: #fff;">
    <div class="xb-loader" style="display: flex; align-items: center; justify-content: center;">
        <div class="xb-loadding-inner" style="text-align: center;">
            <img src="{{ asset('assets/img/logo/preloader2.png')}}" alt="" style="max-height: 150px; max-width: 150px;">
        </div>
    </div>
</div>

<!-- preloader end -->

<div class="body_wrap">

   <!-- header start -->
<header id="home" class="header-area header-default is-sticky">
    <div class="xb-header stricky">
        <div class="container">
            <div class="header__wrap ul_li_between">
                <div class="header-logo">
                    <a href="{{ route('home') }}"><img src="{{ asset('assets/img/logo/supLogo.png')}}" alt="" style="max-height: 120px; max-width: 120px;" class="logo"></a>
                </div>
                <div class="main-menu__wrap ul_li navbar navbar-expand-lg">
                    <nav class="main-menu collapse navbar-collapse">
                        <ul>
                            <li class="menu-item active">
                                <a class="section-link" href="{{ route('home') }}"><span>Home</span></a>
                            </li>
                            <li><a class="section-link" href="{{ route('home') }}#features"><span>Features</span></a></li>
                            <li class="menu-item-has-children">
                                <a class="section-link" href="#shop"><span>Shop</span></a>
                                <ul class="submenu">
                                    <li><a href="{{ route('shop') }}"><span>Products</span></a></li>
                                    <li><a href="/Cart"><span>Cart</span></a></li>
                                    <li><a href="checkout.html"><span>Checkout</span></a></li>
                                </ul>
                            </li>
                            <li><a class="section-link" href="{{ route('home') }}#pricing"><span>Pricing</span></a></li>
                            <li class="menu-item-has-children">
                                <a class="section-link" href="#blog"><span>Blog</span></a>
                                <ul class="submenu">
                                    <li><a href="blog.html"><span>Blog</span></a></li>
                                    <li><a href="blog-single.html"><span>Blog Details</span></a></li>
                                </ul>
                            </li>
                            <li><a class="section-link" href="{{ route('home') }}#contact"><span>Contact</span></a></li>
                        </ul>
                    </nav>
                    <div class="xb-header-wrap">
                        <div class="xb-header-menu">
                            <div class="xb-header-menu-scroll">
                                <div class="xb-menu-close xb-hide-xl xb-close"></div>
                                <div class="xb-logo-mobile xb-hide-xl">
                                    <a href="{{ route('home') }}" rel="home"><img src="assets/img/logo/logo.svg" alt=""></a>
                                </div>
                                <div class="xb-header-mobile-search xb-hide-xl">
                                    <form role="search" action="#">
                                        <input type="text" placeholder="Search..." name="s" class="search-field">
                                    </form>
                                </div>
                                <nav class="xb-header-nav">
                                    <ul class="xb-menu-primary clearfix">
                                        <li class="menu-item active">
                                            <a class="" href="{{ route('home') }}"><span>Home</span></a>
                                        </li>
                                        <li class="menu-item"><a class="section-link" href="{{ route('home') }}#features"><span>Features</span></a></li>
                                        <li class="menu-item menu-item-has-children">
                                            <a class="section-link" href="#shop"><span>Shop</span></a>
                                            <ul class="sub-menu">
                                                <li><a href="{{ route('shop') }}"><span>Products</span></a></li>
                                                <li><a href="shop-single.html"><span>Single Product</span></a></li>
                                                <li><a href="{{ route('cart') }}"><span>Cart</span></a></li>
                                                <li><a href="checkout.html"><span>Checkout</span></a></li>
                                            </ul>
                                        </li>
                                        <li class="menu-item"><a class="section-link" href="{{ route('home') }}#pricing"><span>Pricing</span></a></li>
                                        <li class="menu-item menu-item-has-children">
                                            <a class="section-link" href="#blog"><span>Blog</span></a>
                                            <ul class="sub-menu">
                                                <li><a href="blog.html"><span>Blog</span></a></li>
                                                <li><a href="blog-single.html"><span>Blog Details</span></a></li>
                                            </ul>
                                        </li>
                                        <li class="menu-item"><a class="section-link" href="{{ route('home') }}#contact"><span>Contact</span></a></li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        <div class="xb-header-menu-backdrop"></div>
                    </div>
                </div>
                <div class="header__right d-none d-lg-block">
                    <div class="ul_li">
                        <div class="header-shop-cart">
                            <a href="javascript:void(0);">
                                <img src="{{ asset('assets/img/icon/bag.svg')}}" alt="">
                                <span class="mini-cart-count">2</span>
                            </a>
                            <div class="header-mini-cart" id="mini-cart"></div>
                        </div>
                        <a class="header__bar offcanvas-sidebar-btn" href="javascript:void(0);">
                            <div class="header__bar-icon">
                                <span></span>
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                        </a>
                    </div>
                </div>
                <div class="header-bar-mobile d-lg-none">
                    <a class="header__bar xb-nav-mobile" href="javascript:void(0);">
                        <div class="header__bar-icon">
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
<!-- header end -->

<!-- sidebar-info start -->
<div class="offcanvas-sidebar">
    <div class="sidebar-menu-close">
        <a class="xb-close" href="javascript:void(0);"></a>
    </div>
    <div class="sidebar-top mb-65">
        <div class="sidebar-logo mb-40">
            <a href="index.html">
                <img src="{{ asset('assets/img/logo/preloader2.png')}}" alt="logo" style="max-height: 150px;max-width: 150px">
            </a>
        </div>
        <div class="sidebar-content">
            "Fuel your journey, power your passion—at Supplement Station, where every rep counts."
        </div>
    </div>

    <div class="sidebar-contact-info mb-65">
        <h4 class="sidebar-heading">Contact Information</h4>
        <ul class="sidebar-info-list list-unstyled">
            <li><span><img src="assets/img/icon/i_star.svg" alt=""></span>Mosharfeye ,facing turkey resturant</li>
            <li><a href="#!"><span><img src="assets/img/icon/i_star.svg" alt=""></span>+961 81 088 266</a></li>

        </ul>
    </div>
    <div class="xb-content-wrap d-flex" style="display: flex; align-items: center;">
        <div class="xb-title col-auto" style="margin-right: 20px;">Call us:</div>
        <div class="xb-inf-content-wrap col" style="margin-right: 20px;">
            <div class="xb-item-wrap row">
                <div class="xb-item col-auto">
                    <span class="item-content">
                        <a href="tel:02456787535" class="tel">+961 81 088 266</a>
                    </span>
                </div>
            </div>
        </div>
        <div class="sidebar-socials-wrap" style="margin-top: 0; text-align: center; display: flex; align-items: center; margin-left: auto;">
            <a class="social-item" href="https://www.instagram.com/supplement_station_lb/" target="_blank" style="display: flex; align-items: center;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a5/Instagram_icon.png/2048px-Instagram_icon.png"
                     alt="Instagram"
                     style="width: 24px; height: 24px; margin-right: 10px;">
                Instagram
            </a>
        </div>
    </div>



    <br/><br/>

    <div class="xb-item col-auto">
        <span class="item-content">
            &copy; {{ date('Y') }} Supplement Station. All rights reserved. Powered by <a href="https://www.tawwer.tech/" target="_blank">Tawwer</a>.
        </span>
    </div>
</div>

<!-- sidebar-info end -->




<script>
    function fetchCart() {
        $.ajax({
            url: '{{ route('api.cart.get') }}',
            method: 'GET',
            success: function(response) {
                console.log("response",response.items); // Check response structure
    
                if (!response.items || !Array.isArray(response.items)) {
                    console.error("here");
                    return;
                }
    
                $('#mini-cart').empty(); // Clear previous items
                let total = 0;
    
                if (response.items.length === 0) {
                    $('#mini-cart').append('<p>Your cart is empty.</p>');
                } else {
                    response.items.forEach(item => {
                        if (!item.product || !item.product.price || !item.product.name ) {
                            console.error("here");
                            return;
                        }
    
                        console.log(item.product);
                        const itemTotal = item.product.price * item.quantity;
                        $('#mini-cart').append(`
        <div class="woocommerce-mini-cart-item d-flex align-items-center" style="padding: 10px;">
            <div class="mini-cart-img" style="margin-right: 10px;">
                <img src="https://atlas-content-cdn.pixelsquid.com/assets_v2/265/2653773395304388238/previews/G03-200x200.jpg" alt="${item.product.name}" style="width: 50px; height: 50px; object-fit: cover;">
            </div>
            <div class="mini-cart-content" style="flex-grow: 1;">
                <h4 class="product-title" style="margin: 0; font-size: 14px;">
                    <a href="shop-details.html" style="text-decoration: none; color: #000;">${item.product.name}</a>
                </h4>
                <div class="mini-cart-price" style="margin-top: 5px;">
                    ${item.quantity} ×
                    <span class="woocommerce-Price-amount amount" style="color: red;">$${parseFloat(item.product.price).toFixed(2)}</span>
                </div>
            </div>
            <div class="remove-button" style="margin-left: auto;">
        <a href="#" class="remove remove_from_cart_button" data-product_id="${item.product.id}" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: lightgrey; text-align: center; line-height: 20px; color: red; font-size: 14px;">×</a>
    </div>
    
        </div>
    `);
    
    
                        console.log('Added item:', item.product.name); // Debugging line
                        total += itemTotal;
                    });
    
                    $('#mini-cart').append(`
                        <p class="woocommerce-mini-cart__total">
                            <strong>Subtotal:</strong>
                            <span class="woocommerce-Price-amount">$${total.toFixed(2)}</span>
                        </p>
                        <p class="checkout-link">
                            <a href="/Cart" class="button wc-forward">View cart</a>
                            <a href="checkout.html" class="button checkout wc-forward">Checkout</a>
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
        fetchCart();
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
    
    
    
    
    