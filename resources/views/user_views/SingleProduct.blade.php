@extends('layouts.store')

@section('title', 'Single Product')
@section('page', 'product')
@section('body_wrap', 1)

@section('content')
    <!-- main area start  -->
    <main>
        <!-- breadcrumb start -->
        <section class="breadcrumb position-bottom bg_img" data-background="{{ asset('assets/img/bg/shop-cart-banner.webp')}}">
            <div class="container">
                <div class="breadcrumb__content text-center">
                    <h2 class="breadcrumb__title" style="text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7); color: #fff;">Product</h2>
                    <ul class="breadcrumb__list clearfix">
                        <li class="breadcrumb-item">
                            <a href="{{route('home')}}" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); color: #fff;">
                                Supplement Station
                            </a>
                        </li>
                        <li class="breadcrumb-item" style="text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.7); color: #fff;">
                            Product
                        </li>
                    </ul>
                </div>
            </div>
        </section>

        <!-- breadcrumb end -->

        <!-- shop single start -->
        <section class="shop-single-section pt-115 pb-385">
            @include('partials.subscription-modal', ['delay' => 2000])
{{--            <div id="message-container" style="display: none;"></div>--}}


            <div class="container">
                <div class="row">


                    <div class="col-md-6">
                        <div class="product-single-wrap mb-30">
                            <div class="product_details_img">
                                <div class="tab-content" id="myTabContent">
                                    @foreach($products->images as $index => $image)
                                        <div class="tab-pane {{ $index == 0 ? 'show active' : '' }}" id="tab-{{ $index }}" role="tabpanel">
                                            <div class="pl_thumb">
                                                <img src="{{ asset($image->url) }}"
                                                     alt="{{ $products->name }} image {{ $index + 1 }}"
                                                     style="width: 100%; /* Make the image responsive to the container's width */
                                                            height: auto; /* Maintain aspect ratio */
                                                            display: block;
                                                            object-fit: cover;
                                                            max-width: 100%; /* Ensure it doesn't exceed its container width */
                                                            max-height: 500px; /* Set maximum height */
                                                            " width="500" height="500" @if($index === 0) fetchpriority="high" @else loading="lazy" @endif>
                                            </div>



                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="shop_thumb_tab">
                                <ul class="nav" id="myTab2" role="tablist">
                                    @foreach($products->images as $index => $image)
                                        <li class="nav-item">
                                            <button class="nav-link {{ $index == 0 ? 'active' : '' }}" id="home-tab" data-bs-toggle="tab" data-bs-target="#tab-{{ $index }}" type="button" role="tab" aria-controls="tab-{{ $index }}" aria-selected="{{ $index == 0 ? 'true' : 'false' }}" >
                                                <img src="{{ asset($image->url) }}" alt="{{ $products->name }} thumbnail {{ $index + 1 }}" height="100" width="100" loading="lazy">
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>



            <div class="col-md-6 product-details-col">
                <div class="product-details mb-20">
                    <h2>{{ $product->name ?? 'Product Name' }}</h2>
                    <div class="rating">
                        @for ($i = 0; $i < 5; $i++)
                            <i class="fas fa-star{{ $i < $averageRating ? '' : '-o' }}"></i>
                        @endfor
                        <span>({{ $ratingCount  }} Customer review{{ $ratingCount > 1 ? 's' : '' }})</span>
                    </div>

                    <div class="price">
                        <span class="xb-item--price">
                            @if($product->discount > 0)
                                <span style="text-decoration: line-through; color: gray;">${{ number_format($product->price, 2) }}</span>
                                <span style="color: red;">${{ number_format($product->price - ($product->price * $product->discount / 100), 2) }}</span>
                            @else
                                ${{ number_format($product->price, 2) }}
                            @endif
                        </span>
                    </div>

                    <div class="product-option">




                        <form class="form" action="{{ route('cart.add') }}" method="POST" id="add-to-cart-form" >
                            <div class="product-row">
                                <div>
                                    <input class="product-count" type="number" value="1" name="quantity" min="1" id="quantity">
                                </div>
                                <div class="add-to-cart-btn">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <input type="hidden" name="selected_options" id="selected_options" value="{}">
                                    @if($product->stock)
                                        <button type="submit" class="xb-btn add-to-cart" data-product-id="{{ $product->id }}">
                                            <i class="far fa-shopping-bag"></i> Add to cart
                                        </button>
                                    @else
                                        <button type="button" class="xb-btn add-to-cart" disabled style="opacity: .6; cursor: not-allowed;">
                                            Sold out
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <div id="successAlert" class="alert alert-success mt-3" style="display: none;">
                                <strong>Success!</strong> Image added successfully.
                            </div>
                            <!-- Error Alert -->
                            <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;">
                                <strong>Error!</strong> Failed to add Image. Please try again.
                            </div>
                        </form>
                    </div>

                    <div class="thb-product-meta-after mt-20">
                        <div class="product_meta">
            <span class="posted_in">Categories:
            @if($product->category_id)
                <span>{{ $category?->name }}</span>
            @else
                <span>No categories</span>
            @endif
            </span>

                            <div class="inner-shop-details-bottom">
    <span class="posted_in">
        @php
            $groupedOptions = [];
            foreach ($product->productOptions as $productOption) {
                $groupedOptions[$productOption->option->option_name][] = $productOption;
            }
        @endphp

        @foreach ($groupedOptions as $optionName => $productOptions)
            <div>
                <span>{{ $optionName }}:</span>
                @foreach ($productOptions as $productOption)
                    @if($productOption->stock > 0)
                        <a href="#" class="option-value" data-option-name="{{ $optionName }}" data-option-value="{{ $productOption->option_value }}">
                            {{ $productOption->option_value }}
                        </a>
                    @else
                        <a href="#" class="option-value">{{ $productOption->option_value }} (Out of stock)</a>
                    @endif
                @endforeach
            </div>
        @endforeach
    </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div> <!-- end col -->


                </div>


                <div class="row">
                    <div class="col col-xs-12">
                        <div class="single-product-info">

                            <div class="tablist">
                                <ul class="nav nav-tabs" id="pills-tab" role="tablist">
                                    <li><button  class="active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#tb-01">Product Details</button></li>
                                    <li><button  id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#tb-03">Review ({{$ratingCount}})</button></li>
                                </ul>
                            </div>


                            <div class="tab-content" id="pills-tabContent">
                                <div class="tab-pane fade show active" id="tb-01">
                                    <p>{{ $product->description ?? 'Product description' }}</p>
                                </div>
                                <div class="tab-pane fade" id="tb-02">
                                    <p>Travelling salesman and above it there hung a picture that he had recently cut out of an illustrated magazine and housed in a nice, gilded frame. It showed a lady fitted out with a fur hat and fur boa who sat upright, raising a heavy fur muff that covered the whole of her lower arm towards the viewer</p>
                                    <p> waved about helplessly as he looked. "What's happened to me?" he thought. It wasn't a dream. His room, a proper human room although a little too small, lay peacefully between its four familiar wallstrated magazine and housed in a nice, gilded frame. It showed a lady fitted out with a fur hat and fur boa who sat upright, raising a heavy fur muff that covered the whole of her lower arm towards the viewer. Gregor then turned to look out the window at the dull weather</p>
                                </div>
                                <div class="tab-pane fade" id="tb-03">
                                    <div class="row">
                                        <div class="col-lg-6 col-sm-12 col-xs-12">



                                            <h3 style="font-size: 24px; font-weight: bold; color: #333; margin-bottom: 20px;">Reviews</h3>

                                            <div class="reviews-section" id="reviewsSection" style="max-height: 400px; overflow-y: auto;">
                                                @foreach($reviews as $review)
                                                <div class="client-rv">
                                                        {{-- phone and wider-screen avatar sizes; the screen width picks one --}}
                                                        <div class="d-md-none">
                                                            <img src="{{ asset('assets/img/icon/profile.png') }}" alt="Client Avatar" style="height: 60px;width:60px" loading="lazy">
                                                        </div>
                                                        <div class="client-pic d-none d-md-block">
                                                            <img src="{{ asset('assets/img/icon/profile.png') }}" alt="Client Avatar" style="width:100px;height:60px" loading="lazy">
                                                        </div>

                                                    <div class="details">
                                                        <div class="name-rating-time" style="display: flex; flex-direction: column;">
                                                            <div class="name-rating" style="display: flex; align-items: center;">
                                                                <h4 style="font-weight: bold; color: black; margin: 0;">{{ $review->name }}</h4>
                                                            </div>
                                                            <div class="review-body" style="margin-top: 5px;">
                                                                <p>{{ $review->message }}</p>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endforeach
                                            </div>


                                        </div>

                                        <div class="col-lg-6 col-sm-12 col-xs-12 review-form-wrapper">
                                            <div class="review-form">
                                                <h4>Here you can review the item</h4>
                                                <form id="reviewForm" data-url="{{ route('product.createReview', $product->id) }}">
                                                    @csrf <!-- CSRF token for security -->
                                                    <div>
                                                        <input type="text" class="form-control" name="name" placeholder="Name *" required>
                                                    </div>
{{--                                                    <div>--}}
{{--                                                        <input type="text" class="form-control" name="phoneNumber" placeholder="Phone *" required>--}}
{{--                                                    </div>--}}
                                                    <div>
                                                        <textarea class="form-control" name="message" placeholder="Review *" required></textarea>
                                                    </div>
                                                    <div class="rating-wrapper">
                                                        <div class="rating" style="display: flex; gap: 5px;">
                                                            @for ($i = 1; $i <= 5; $i++)
                                                            <label style="cursor: pointer;">
                                                                <input type="radio" name="rating" value="{{ $i }}" style="display: none;">
                                                                <i class="fal fa-star star" onclick="gfg({{ $i }})" style="font-size: 24px; transition: color 0.3s ease-in-out;"></i>
                                                            </label>
                                                            @endfor
                                                        </div>
                                                        <div class="submit">
                                                            <button class="thm-btn thm-btn--black" type="submit">
                                                                <span class="btn-wrap">
                                                                    <span>Submit</span>
                                                                </span>
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                                <!-- Success message container -->
                                                <div id="successMessage" style="color: green; margin-top: 10px; display: none;"></div>
                                            </div>
                                        </div>






                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> <!-- end row -->


                    <!-- Mobile Layout -->
                <br>
                <br>
                <br>
                <br>
                <br>
                    <div class="container">
                        <div class="sec-title text-center mb-55">
                            <h2 class="title">Related products</h2>
                        </div>
                        <div class="product-slider swiper-container"  >
                            <div class="swiper-wrapper" >
                                @foreach($relatedProducts as $item)
                                    <div class="swiper-slide product-item text-center" style="min-height: 400px">
                                        <div class="xb-item--img">
                                            @if($item->discount > 0)
                                                <div class="ribbon" style="
                            width: 150px;
                            height: 150px;
                            overflow: hidden;
                            position: absolute;
                            top: -10px;
                            right: 0px;
                            z-index: 2;
                        ">
                            <span style="
                                position: absolute;
                                display: block;
                                width: 225px;
                                padding: 15px 0;
                                background-color: #A02334;
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
                                            @if($item->stock > 0)
                                                <a href="{{ route('products.show', $item->id) }}">
                                                    <!-- Add image here -->
                                                    @if($item->images->isNotEmpty())
                                                        <img src="{{ asset($item->images->first()->url) }}" alt="img" style="max-height: 120px" loading="lazy">
                                                    @else
                                                        No image available
                                                    @endif
                                                </a>
                                            @else
                                                <a href="{{ route('products.show', $item->id) }}" class="no-stock">
                                                    <!-- Add image here -->
                                                    @if($item->images->isNotEmpty())
                                                        <img src="{{ asset($item->images->first()->url) }}" alt="img" style="max-height: 120px" loading="lazy">
                                                    @else
                                                        No image available
                                                    @endif
                                                </a>
                                            @endif
                                        </div>
                                        <div class="xb-item--holder">
                                            <h3 class="xb-item--title">
                                                @if($item->stock > 0)
                                                    <a href="{{ route('products.show', $item->id) }}">{{ $item->name }}</a>
                                                @else
                                                    <a href="{{ route('products.show', $item->id) }}"><span>{{ $item->name }}<span style="opacity: 0.8; color: #ff0000;"><br/>(out of stock)</span></span></a>

                                                @endif
                                            </h3>
                                            @php
                                                $ratingCount = $item->reviews_count;
                                                $averageRating = $ratingCount > 0 ? $item->reviews_avg_rating : 5;
                                            @endphp
                                            <div class="xb-item--rating-inner ul_li_center">
                                                <ul class="xb-item--rating ul_li">
                                                    @for ($i = 0; $i < 5; $i++)
                                                        <i class="fas fa-star{{ $i < $averageRating ? '' : '-o' }}"></i>
                                                    @endfor
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="xb-item--action ul_li mt-20" style="position: absolute; bottom: 30px; width: 80%;">
                        <span class="xb-item--price">
                            @if($item->discount > 0)
                                <span style="text-decoration: line-through; color: gray;">${{ number_format($item->price, 2) }}</span>
                                <span style="color: #A02334;">${{ number_format($item->price - ($item->price * $item->discount / 100), 2) }}</span>
                            @else
                                ${{ number_format($item->price, 2) }}
                            @endif
                        </span>
                                            @if($item->stock > 0)
                                                <a href="{{ route('products.show', $item->id) }}" class="xb-item--cart-btn">
                                                    <span class="xb-item--cart-icon"><img src="{{asset('assets/img/icon/bag.svg')}}" alt="" loading="lazy"></span>
                                                    <span class="xb-item--cart">add to cart</span>
                                                </a>
                                            @elseif($item->stock == 0)
                                                <a href="{{ route('products.show', $item->id) }}" class="xb-item--cart-btn disabled" style="color: lightgrey" >
                                                    <span class="xb-item--cart-icon"><img src="{{asset('assets/img/icon/bag.svg')}}" alt="" loading="lazy"></span>
                                                    <span class="xb-item--cart">Out of Stock</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
{{--                                @empty--}}
{{--                                    <p>No products available.</p>--}}
                                        @endforeach
                            </div>
                        </div>
                    </div>

            </div> <!-- end of container -->
        </section>
        <!-- shop single start -->

    </main>
    <!-- main area end  -->
@endsection
