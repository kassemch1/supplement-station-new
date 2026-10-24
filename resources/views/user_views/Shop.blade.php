@extends('layouts.store')

@php
    $heading = $category ? $category->name : 'All Supplements';
    $pageSuffix = $product->currentPage() > 1 ? ' - Page ' . $product->currentPage() : '';
    $intro = $category
        ? "Shop {$category->name} in Lebanon at Supplement Station: {$totalProducts} " . \Illuminate\Support\Str::plural('product', $totalProducts) . ', with prices in USD. Order online or visit our store in Beirut.'
        : null;
@endphp
@section('title', ($category ? $category->name . ' in Lebanon' : 'Shop All Supplements in Lebanon') . $pageSuffix)
@section('description', $category
    ? \App\Support\Seo::text("Buy {$category->name} in Lebanon at Supplement Station, Beirut. {$totalProducts} " . \Illuminate\Support\Str::plural('product', $totalProducts) . ' from trusted brands. Order online.')
    : 'Shop protein, creatine, mass gainers, pre-workouts, amino acids, vitamins, fat burners and gym gear in Lebanon. Order online at Supplement Station, Beirut.')
@section('canonical', $canonical)
@if($noindex)
    @section('robots', 'noindex, follow')
@endif
@push('head')
    @if($prevUrl)<link rel="prev" href="{{ $prevUrl }}">@endif
    @if($nextUrl)<link rel="next" href="{{ $nextUrl }}">@endif
@endpush
@push('schema')
    {{ \App\Support\Seo::script([
        \App\Support\Seo::collection($heading, $canonical, $product->items(), $startIndex),
        \App\Support\Seo::breadcrumbs(array_filter([
            ['Home', route('home')],
            ['Shop', route('shop')],
            $category ? [$category->name, $category->url()] : null,
        ])),
    ]) }}
@endpush
@section('page', 'shop')

@section('content')
@include('partials.subscription-modal', ['delay' => 3000])
<!-- main area start  -->
<main>
    <!-- breadcrumb start -->
    <section class="breadcrumb position-bottom bg_img" data-background="{{ asset('assets/img/bg/shop-cart-banner.webp')}}">
        <div class="container">
            <div class="breadcrumb__content text-center">
                <h1 class="breadcrumb__title" style="text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7); color: #fff;">{{ $heading }}</h1>
                <ul class="breadcrumb__list clearfix">
                    <li class="breadcrumb-item">
                        <a href="{{route('home')}}" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); color: #fff;">
                            Supplement Station
                        </a>
                    </li>
                    @if($category)
                    <li class="breadcrumb-item">
                        <a href="{{ route('shop') }}" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); color: #fff;">Shop</a>
                    </li>
                    @endif
                    <li class="breadcrumb-item" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); color: #fff;" aria-current="page">
                        {{ $category?->name ?? 'Shop' }}
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
                                            <a href="{{ route('shop') }}" class="btn btn-link category-button {{ $category ? '' : 'active' }}" style="text-decoration: none;color: {{ $category ? 'inherit' : '#A02334' }}">Show All</a>
                                        </li>
                                        @foreach($categories as $item)
                                            <li>
                                                <a href="{{ $item->url() }}" class="btn btn-link category-button {{ $category?->id === $item->id ? 'active' : '' }}" style="text-decoration: none;color: {{ $category?->id === $item->id ? '#A02334' : 'inherit' }}">{{ $item->name }}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                                <div class="widget__inner d-md-none"
                                     style="max-height: 300px; overflow-y: auto; padding-right: 10px; box-sizing: border-box;">
                                    <ul class="widget__category list-unstyled">
                                            <li>
                                                {{-- choosing a category opens its page (shop.js) --}}
                                                <select id="category-phone" aria-label="Product category">
                                                    <option value="{{ route('shop') }}">Show All</option>
                                                    @foreach($categories as $item)
                                                        <option value="{{ $item->url() }}" {{ $category?->id === $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                                    @endforeach
                                                </select>
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
                        @if($intro && !$search && $product->currentPage() === 1)
                            <p class="mb-30">{{ $intro }}</p>
                        @endif
                        <div class="woocommerce-toolbar-top ul_li_between">
                            <div id="products_indexing">
                                @include('partials/products_indexing')
                            </div>
                            <div class="woocommerce-toolbar-top-right ul_li">
                                <form id="sort-form" class="woocommerce-ordering" method="get" action="{{ $category ? $category->url() : route('shop') }}">
                                    <select name="orderby" class="orderby" id="sort-by-price">
                                        <option value="sort" selected="selected">Apply sorting</option>
                                        <option value="default">Default</option>
                                        <option value="low-to-high">Sort by price: low to high</option>
                                        <option value="high-to-low">Sort by price: high to low</option>
                                    </select>
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
