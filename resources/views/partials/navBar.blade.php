
<!-- preloader start -->
<div id="xb-loadding" class="xb-loadding-container" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; z-index: 9999; background-color: #fff;">
    <div class="xb-loader" style="display: flex; align-items: center; justify-content: center;">
        <div class="xb-loadding-inner" style="text-align: center;">
            <img src="{{ asset('assets/img/logo/preloader2.png')}}" alt="" style="max-height: 250px; max-width: 250px;">
        </div>
    </div>
</div>

<!-- preloader end -->

<div class="body_wrap">

   <!-- header start -->
<header id="home" class="header-area header-default is-sticky" >
    <div class="xb-header stricky" >
        <div class="container" >
            <div class="header__wrap ul_li_between">
                <div class="header-logo">
                    <a href="{{ route('home') }}"><img src="{{ asset('assets/img/logo/outlined-logo-nav.png')}}" alt="" style="width: 190px;height: auto" class="logo"></a>
                </div>
                <div class="main-menu__wrap ul_li navbar navbar-expand-lg">
                    <nav class="main-menu collapse navbar-collapse">
                        <ul>
                            <li class="menu-item {{ url()->current() ==route('home')? 'active' : '' }}">
                                <a class="section-link" href="{{ route('home') }}"><span>Home</span></a>
                            </li>



                                    <li class="{{ url()->current() ==route('shop')? 'active' : '' }}"><a href="{{ route('shop') }}"><span>Products</span></a></li>
                                    <li class="{{ url()->current() ==route('cart')? 'active' : '' }}"><a href="{{ route('cart') }}"><span>Cart</span></a></li>




                            <li class="menu-item-has-children ">
                                <a class="section-link" href="#"><span>Categories</span></a>
                            <ul class="submenu"  >
                                    @foreach($categories as $category)
                                        <form action="{{route('shop')}}" >
                                            @csrf
                                            <input type="hidden" name="category-nav" value="{{$category->id}}">
                                            <li><a href="#" onclick="this.closest('form').submit(); return false;"><span >{{$category->name}}</span></a></li>

                                        </form>
                                    @endforeach
                                </ul>
                            </li>
                            <li><a class="section-link" href="{{ route('home') }}#pricing"><span>Plans</span></a></li>
                            <li><a class="section-link" href="{{ route('home') }}#contact"><span>Contact</span></a></li>
                        </ul>
                    </nav>
                    <div class="xb-header-wrap">
                        <div class="xb-header-menu">
                            <div class="xb-header-menu-scroll">
                                <div class="xb-menu-close xb-hide-xl xb-close"></div>
                                <div class="xb-logo-mobile xb-hide-xl">
                                    <a href="{{ route('home') }}"><img src="{{ asset('assets/img/logo/supplement-logo-nav.png')}}" alt="" style="max-height: 180px; max-width: 120px;" class="logo"></a>
                                </div>
                                <div class="xb-header-mobile-search xb-hide-xl">
                                    <form role="search" action="{{route('shop')}}" method="get">
                                        @csrf
                                        <input type="text" placeholder="Search..." name="home-search" class="search-field">
                                    </form>
                                </div>
                                <nav class="xb-header-nav">
                                    <ul class="xb-menu-primary clearfix">
                                        <li class="menu-item active">
                                            <a class="" href="{{ route('home') }}"><span>Home</span></a>
                                        </li>
                                        <li class="menu-item"><a class="section-link" href="{{ route('home') }}#features"><span>Features</span></a></li>

                                                <li><a href="{{ route('shop') }}"><span>Products</span></a></li>
                                                <li><a href="{{ route('cart') }}"><span>Cart</span></a></li>



                                        <li class="menu-item menu-item-has-children">
                                            <a class="section-link" href="#"><span>Categories</span></a>
                                            <ul class="sub-menu">
                                                @foreach($categories as $category)
                                                    <form action="{{route('shop')}}">
                                                        @csrf
                                                        <input type="hidden" name="category-sidebar" value="{{$category->id}}">
                                                        <li><button type="submit" class="btn btn-link" style="text-decoration: none;color: inherit"><span>{{$category->name}}</span></button></li>

                                                    </form>
                                                @endforeach
                                            </ul>
                                        </li>
                                        <li class="menu-item"><a class="section-link" href="{{ route('home') }}#plans"><span>plans</span></a></li>
                                        <li class="menu-item"><a class="section-link" href="{{ route('home') }}#contact"><span>Contact</span></a></li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                        <div class="xb-header-menu-backdrop"></div>
                    </div>
                </div>
                <div class="header__right d-none d-lg-block" >
                    <div class="ul_li">
                        <div class="header-shop-cart">
                            <a href="javascript:void(0);"><img src="{{ asset('assets/img/icon/bag.svg')}}" alt=""><span class="mini-cart-count">0</span></a>
                            <div class="header-mini-cart" >


                            </div>
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
                <div class="ul_li">
                <div class="header-shop-cart d-lg-none">
                    <a href="javascript:void(0);"><img src="{{ asset('assets/img/icon/bag.svg')}}" alt=""><span class="mini-cart-count">0</span></a>
                    <div class="header-mini-cart" >


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
            <br><br><br>
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
            <li><span><img src="assets/img/icon/i_star.svg" alt=""></span>Mosharfeye,Facing turkey resturant</li>
            <li><a href="#!"><span><img src="assets/img/icon/i_star.svg" alt=""></span>+961 81-823-038</a></li>

        </ul>
    </div>
    <div class="xb-content-wrap d-flex" style="display: flex; align-items: center;">
        <div class="xb-title col-auto" style="margin-right: 20px;">Call us:</div>
        <div class="xb-inf-content-wrap col" style="margin-right: 20px;">
            <div class="xb-item-wrap row">
                <div class="xb-item col-auto">
                    <span class="item-content">
                        <a href="tel:02456787535" class="tel">+961 81-823-038</a>
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




