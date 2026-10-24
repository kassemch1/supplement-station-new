@extends('layouts.store')

@section('title', 'Shop')
@section('page', 'shop')

@section('content')
@include('partials.subscription-modal', ['delay' => 3000])
<!-- main area start  -->
<main>
    <!-- breadcrumb start -->
    <section class="breadcrumb position-bottom bg_img" data-background="{{ asset('assets/img/bg/shop-cart-banner.webp')}}">
        <div class="container">
            <div class="breadcrumb__content text-center">
                <h2 class="breadcrumb__title" style="text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7); color: #fff;">Shop</h2>
                <ul class="breadcrumb__list clearfix">
                    <li class="breadcrumb-item">
                        <a href="{{route('home')}}" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); color: #fff;">
                            Supplement Station
                        </a>
                    </li>
                    <li class="breadcrumb-item" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); color: #fff;">
                        Shop
                    </li>
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
                                <div class="widget__inner d-none d-md-block"
                                     style="max-height: 300px; overflow-y: auto; padding-right: 10px; box-sizing: border-box;">
                                    <ul class="widget__category list-unstyled">
                                        <li>
                                                <form action="{{route('shop')}}" method="GET" class="category-form">
                                                    @csrf
                                                    <input type="hidden" name="category" value="show-all">
                                                    <button type="submit" class="btn btn-link category-button" style="text-decoration: none;color: inherit">
                                                        Show All
                                                    </button>

                                                </form>
                                        </li>
                                        @foreach($categories as $category)
                                            <li>
                                                <form action="{{route('shop')}}" method="GET" class="category-form">
                                                    @csrf
                                                    <input type="hidden" name="category" value="{{ $category->name }}">
                                                    <button type="submit" class="btn btn-link category-button" style="text-decoration: none;color: inherit">
                                                        {{ $category->name }}
                                                    </button>

                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="widget__inner d-md-none"
                                     style="max-height: 300px; overflow-y: auto; padding-right: 10px; box-sizing: border-box;">
                                    <ul class="widget__category list-unstyled">
                                            <li>
                                                <form action="{{route('shop')}}" method="GET" class="category-form-phone">
                                                    @csrf
                                                    <select name="category-phone" id="category-phone">
                                                        <option value="all" >Show All</option>
                                                        @foreach($categories as $category)
                                                            <option value="{{$category->name}}" {{ $currentCategoryId === $category->id ? 'selected' : '' }}>{{$category->name}}</option>
                                                        @endforeach
                                                    </select>

                                                </form>
                                            </li>
                                    </ul>
                                </div>


                        </div>
                        @if($offersProducts)
                            @include('partials.shop_offers', ['class' => 'd-none d-md-block'])
                        @endif

                        <!--End Of the Offers Section -->


                    </div>
                </div>
                <div class="col-lg-9 mt-60">
                    <div class="woocommerce-content-wrap">
                        <div class="woocommerce-toolbar-top ul_li_between">
                            <div id="products_indexing">
                                @include('partials/products_indexing')
                            </div>
                            <div class="woocommerce-toolbar-top-right ul_li">
                                <form id="sort-form" class="woocommerce-ordering" method="get" action="{{route('shop')}}">
                                    @csrf
                                    <input type="hidden" name="category" id="category-input" value="">
                                    <select name="orderby" class="orderby" id="sort-by-price">
                                        <option value="sort" selected="selected">Apply sorting</option>
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
                                @include('partials/product_list',['product'=>$product])
                            </div>

                            <div id="pagination">
                                @include('partials/product_pagination',['product'=>$product])
                            </div>


                        </div>
                        <br>
                        <br>
                    </div>
                </div>

                <div class="row mt-none-60">
                    <div class="col-lg-3 mt-60">
                        <div class="shop-sidebar sidebar-area mt-none-40">
                @if($offersProducts)
                    @include('partials.shop_offers', ['class' => 'd-md-none'])
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
@endsection
