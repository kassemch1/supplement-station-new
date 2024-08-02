
<!doctype html>
<html lang="zxx">
<head>

    <!--========= Required meta tags =========-->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Purefit - Health Supplement Landing Page</title>

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

    <!-- sidebar-info start -->
    <div class="offcanvas-sidebar">
        <div class="sidebar-menu-close">
            <a class="xb-close" href="javascript:void(0);"></a>
        </div>
        <div class="sidebar-top mb-65">
            <div class="sidebar-logo mb-40">
                <a href="index.html">
                    <img src="{{ asset('assets/img/logo/logo_black.svg')}}" alt="logo">
                </a>
            </div>
            <div class="sidebar-content">
                Achieving optimal nutrition is a complex endeavor without the inclusion of supplementary support
            </div>
        </div>

        <div class="sidebar-contact-info mb-65">
            <h4 class="sidebar-heading">Contact Information</h4>
            <ul class="sidebar-info-list list-unstyled">
                <li><span><img src="{{ asset('assets/img/icon/i_star.svg')}}" alt=""></span>Wasington SY, UK, NY 12099</li>
                <li><a href="#!"><span><img src="{{ asset('assets/img/icon/i_star.svg')}}" alt=""></span>+81 800 123 06 78</a></li>
                <li><a href="#!"><span><img src="{{ asset('assets/img/icon/i_star.svg')}}" alt=""></span>contact@purefit.com</a></li>
            </ul>
        </div>
        <div class="xb-content-wrap d-flex">
            <div class="xb-title col-auto">Call us:</div>
            <div class="xb-inf-content-wrap col">
                <div class="xb-item-wrap row">
                    <div class="xb-item col-auto ">
                        <span class="item-content"><a href="tel:02456787535" class="tel">024 5678 7535</a></span>
                    </div>
                    <div class="xb-item col-auto "> <span class="item-content"><a href="mailto:support@gmail.com">contact@purefit.com</a></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="sidebar-socials-wrap mt-30"> <a class="social-item" href="https://facebook.com/" target="_blank">Facebook</a><a class="social-item" href="https://www.behance.net/" target="_blank">Behance</a><a class="social-item" href="#" target="_blank">Telegram</a><a class="social-item" href="https://dribbble.com/" target="_blank">Dribbble</a></div>

    </div>
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
                        <li class="breadcrumb-item"><a href="index.html">Purefit</a></li>
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
                                                <div class="product product-item text-center">
                                                    <div class="xb-item--img">
                                                        @if($item->getImages->isNotEmpty())
                                                        <img src="{{asset($item->getImages->first()->url)}}" alt="img">
                
                                                    @else
                                                        No image available
                                                    @endif
                                                    </div>
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
                                                        <span class="xb-item--price">${{ number_format($item->price, 2) }}</span>
                                                        <a href="shop-single.html">
                                                            <span class="xb-item--cart-icon"><img src="{{ asset('assets/img/icon/bag.svg')}}" alt="Cart"></span>
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
                            <div class="widget widget_price_filter mt-40">
                                <h2 class="widget__title">Filter By Price</h2>
                                <div class="widget__inner">
                                    <div class="filter-price">
                                        <form>
                                            <div id="slider-range"></div>
                                            <p>Price : <input type="text" id="amount"></p>
                                            <button>filter</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <div class="widget mt-40">
                                <h2 class="widget__title">
                                    <span>Tags</span>
                                </h2>
                                <div class="widget__inner">
                                    <div class="tagcloud">
                                        <a href="#!">energy</a>
                                        <a href="#!">fitness</a>
                                        <a href="#!">healthy</a>
                                        <a href="#!">powders</a>
                                        <a href="#!">nutrition</a>
                                        <a href="#!">snacks</a>
                                        <a href="#!">wellness</a>
                                        <a href="#!">powders</a>
                                    </div>
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
    <footer class="position-top bg_img pb-70" data-background="assets/img/bg/footer_bg.png">
        <div class="container">
            <div class="contact pb-100">
                <div class="row">
                    <div class="col-lg-7">
                        <div class="xb-contact contact-mt--255">
                            <div class="contact-title mb-35">
                                <span><img src="{{ asset('assets/img/icon/directbox-notif.svg')}}" alt="">Contact Us</span>
                                <h3>Do you have questions or went more <br> information?</h3>
                            </div>
                            <form class="contact-from" action="#!">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="xb-item--field">
                                            <span><img src="assets/img/icon/c_user.svg" alt=""></span>
                                            <input type="text" placeholder="Steven Kevin">
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="xb-item--field">
                                            <span><img src="assets/img/icon/c_mail.svg" alt=""></span>
                                            <input type="text" placeholder="purefit@gmail.com">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="xb-item--field">
                                            <span><img src="assets/img/icon/c_call.svg" alt=""></span>
                                            <input type="text" placeholder="+91 081 0256 023">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="xb-item--field">
                                            <span><img src="assets/img/icon/c_message.svg" alt=""></span>
                                            <textarea name="message" id="message" cols="30" rows="10"
                                                      placeholder="Write Your Message..."></textarea>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button class="thm-btn thm-btn--black" type="submit">Send Message</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="contact-info contact-mt--255 mt-md-30">
                            <div class="xb-item--head">
                                <div class="xb-item--address">
                                    <h3><img src="{{ asset('assets/img/icon/location.svg')}}" alt="">our address</h3>
                                    <p>100 Orchard st, New <br> York,NY 100025 USA</p>
                                </div>
                                <div class="xb-item--open">
                                    <p>Monday - Friday <br>
                                        08:00AM - 05:00PM</p>
                                    <a href="mailto:purefit@gmail.com"><img src="assets/img/icon/sms-tracking.svg" alt="">purefit@gmail.com</a>
                                </div>
                                <ul class="xb-item--social ul_li mt-30">
                                    <li><a href="#!"><i class="fab fa-telegram-plane"></i></a></li>
                                    <li><a href="#!"><i class="fab fa-whatsapp"></i></a></li>
                                    <li><a href="#!"><i class="fab fa-facebook-f"></i></a></li>
                                </ul>
                            </div>
                            <div class="xb-item--cta" data-background="{{ asset('assets/img/bg/cta_bg.jpg')}}">
                                <p>Our help desk is a vailable for you <br> every day, 07:00AM - 10:00PM</p>
                                <h3>+91 081 256 023</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="footer-inner">
                <div class="footer-logo mb-25 text-center">
                    <img src="assets/img/logo/f_logo.png" alt="">
                </div>
                <div class="sec-title sec-title--white text-center mb-50">
                    <h2 class="title">in a healthy body, healthy mind</h2>
                </div>
                <ul class="footer-nav ul_li_center">
                    <li><a href="#!">all products</a></li>
                    <li><a href="#!">track order</a></li>
                    <li><a href="#!">my account</a></li>
                    <li><a href="#!">gift cards</a></li>
                    <li><a href="#!">our story</a></li>
                    <li><a href="#!">careers</a></li>
                    <li><a href="#!">contact</a></li>
                </ul>
                <div class="footer-bottom mt-50 ul_li_between">
                    <div class="footer-copyright mt-30">
                        Copyright © 2024 purefit All rights reserved.
                    </div>
                    <ul class="footer-links ul_li mt-30">
                        <li><a href="#!">terms of conditions</a></li>
                        <li><a href="#!">privacy pllicy</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>
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


<!-- Mirrored from html.xpressbuddy.com/purefit/shop.html by HTTrack Website Copier/3.x [XR&CO'2014], Sat, 27 Jul 2024 09:34:25 GMT -->
</html>
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
                    productHtml += `
                        <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                            <div class="product product-item text-center">
                                <div class="xb-item--img">
                                    <a href="shop-single.html">
                                        <img src="https://www.pngitem.com/pimgs/m/400-4009272_whey-protein-sascha-fitness-caramel-hd-png-download.png" alt="${product.name}">
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
    
































