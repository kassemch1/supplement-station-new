<!doctype html>
<html lang="en">


<head>

    <!--========= Required meta tags =========-->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Single Product | Supplement Station</title>

    <link rel="shortcut icon" href={{ asset('assets/img/logo/preloader2.png')}} type="images/x-icon"/>
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('assets/img/logo/preloader2.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('assets/img/logo/preloader2.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('assets/img/logo/preloader2.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/logo/preloader2.png') }}">

    <!-- css include -->
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/fontawesome.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/animate.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/swiper.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/jquery-ui.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/main.css') }}">
    <style>
        .selected {
            font-weight: bold;
            background-color: #A02334;
            color: white;
        }
        .inner-shop-details-bottom {
            margin-top: 23px;
            padding-top: 23px;
            border-top: 1px solid #ebebeb;
            font-size: 16px;
        }
        .inner-shop-details-bottom > span {
            display: block;
            margin-bottom: 10px;
            color: #000000;
            font-weight: 500;
            margin-right: 5px;
        }
        .inner-shop-details-bottom > span:last-child {
            margin-bottom: 0;
        }
        .inner-shop-details-bottom > span > span {
            font-weight: 500;
            margin-left: 5px;
        }
        .inner-shop-details-bottom span a {
            color: #777777;
            margin-left: 5px;
            border: 1px solid #ebebeb;
            font-weight: 400;
            padding: 3px 10px;
            font-size: 14px;
            margin-bottom: 8px;
            margin-right: 3px;
            border-radius: 5px;
            display: inline-block;
        }
        .inner-shop-details-bottom span a:hover {
            color: #fff;
            background: #A02334;
            border-color: #A02334;
        }

        #successAlert {
            background-color: transparent;
            color: green;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: none; /* Hidden by default */
        }

        #errorAlert {
            background-color: transparent;
            color: #F44336;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: none; /* Hidden by default */
        }
        .add-to-cart-button-disabled {
            width: 100%;
            padding: 12px;
            background-color: grey;
            color: #fff;
            border: none;
            /*border-radius: 4px;*/
            font-size: 14px; /* Ensure consistent font size */
            cursor: pointer;
            transition: background-color 0.3s;
            margin-top: auto;
            align-items: center;
            justify-content: center;
            align-self: center;
            text-align: center;
            display: flex;
            align-items: center;
            white-space: nowrap; /* Prevent text from wrapping */
        }
        .disabled {
            pointer-events: none; /* Disable the link */
            color: lightgrey !important; /* Ensure the color changes */
            background-color: #f5f5f5; /* Optionally change the background */
            cursor: not-allowed; /* Show a "not allowed" cursor */
            opacity: 0.6; /* Reduce opacity for a more 'disabled' look */
        }

        .disabled .xb-item--cart-icon img {
            opacity: 0.6; /* Make the icon look disabled too */
        }

        .subscription-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
        }

        .subscription-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .subscription-modal-content {
            background: white;
            border-radius: 10px;
            padding: 0;
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            position: relative;
            animation: modalSlideIn 0.3s ease-out;
        }

        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .subscription-modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #A02334;
            color: white;
            border-radius: 10px 10px 0 0;
        }

        .subscription-modal-header h3 {
            margin: 0;
            font-size: 18px;
        }

        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: white;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-modal:hover {
            opacity: 0.7;
        }

        .subscription-modal-body {
            padding: 20px;
        }

        .subscription-modal-body p {
            margin-bottom: 20px;
            color: #666;
            text-align: center;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group input:focus {
            outline: none;
            border-color: #A02334;
            box-shadow: 0 0 5px rgba(160, 35, 52, 0.3);
        }

        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-subscribe, .btn-cancel {
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }

        .btn-subscribe {
            background-color: #A02334;
            color: white;
        }

        .btn-subscribe:hover {
            background-color: #8a1e2c;
        }

        .btn-cancel {
            background-color: #6c757d;
            color: white;
        }

        .btn-cancel:hover {
            background-color: #5a6268;
        }

        @media (max-width: 768px) {
            .subscription-modal-content {
                margin: 20px;
                width: calc(100% - 40px);
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-subscribe, .btn-cancel {
                width: 100%;
            }
        }

    </style>
</head>

<body>

<!-- backtotop - start -->
<div class="xb-backtotop">
    <a href="#" class="scroll">
        <i class="far fa-arrow-up"></i>
    </a>
</div>




<div class="body_wrap">

    @include('partials/navBar',['categories'=>$categories])


    <div class="body-overlay"></div>

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
            <!-- Subscription Modal -->
            <div id="subscriptionModal" class="subscription-modal" style="display: none;">
                <div class="subscription-modal-overlay">
                    <div class="subscription-modal-content">
                        <div class="subscription-modal-header">
                            <h3>Stay updated! &#10084;</h3>
                            <button class="close-modal" id="closeModal">&times;</button>
                        </div>
                        <div class="subscription-modal-body">
                            <p>Stay updated on the latest deals, exclusive offers, new product alerts, and free shipping opportunities tips delivered to your inbox!</p>
                            <form id="subscriptionForm">
                                <div class="form-group">
                                    <input type="email" id="subscriptionEmail" name="email" placeholder="Enter your email address" required>
                                </div>
                                <div class="form-actions">
                                    <button type="submit" class="btn-subscribe">Subscribe</button>
                                    <button type="button" class="btn-cancel" id="cancelSubscription">No, Thanks</button>
                                </div>
                            </form>
                            <div id="subscriptionMessage" style="display: none; margin-top: 10px;"></div>
                        </div>
                    </div>
                </div>
            </div>
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
                                                            " loading="lazy">
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
                                    <button type="submit" class="xb-btn add-to-cart" data-product-id="{{ $product->id }}">
                                        <i class="far fa-shopping-bag"></i> Add to cart
                                    </button>
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
                <span>{{ $category->name }}</span>
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

                                                        @if($agent->isMobile())
                                                        <div>
                                                            <img src="{{ asset('assets/img/icon/profile.png') }}" alt="Client Avatar" style="height: 60px;width:60px" loading="lazy">
                                                        </div>
                                                        @else
                                                        <div class="client-pic">
                                                        <img src="{{ asset('assets/img/icon/profile.png')}}" alt="Client Avatar" style="width:100px;height:60px" loading="lazy">
                                                    </div>
                                                        @endif

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
                                                <form id="reviewForm">
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


{{--                @if($agent->isMobile())--}}
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
                                                <div class="no-stock">
                                                    <!-- Add image here -->
                                                    @if($item->images->isNotEmpty())
                                                        <img src="{{ asset($item->images->first()->url) }}" alt="img" style="max-height: 120px" loading="lazy">
                                                    @else
                                                        No image available
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                        <div class="xb-item--holder">
                                            <h3 class="xb-item--title">
                                                @if($item->stock > 0)
                                                    <a href="{{ route('products.show', $item->id) }}">{{ $item->name }}</a>
                                                @else
                                                    <span>{{ $item->name }}<span style="opacity: 0.8; color: #ff0000;"><br/>(out of stock)</span></span>

                                                @endif
                                            </h3>
                                            @php
                                                $ratingCount = $item->reviews()->count();
                                                $averageRating = $ratingCount > 0 ? $item->reviews()->avg('rating') : 5;
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
                                                <a href="{{ route('products.show', $product->id) }}" class="xb-item--cart-btn">
                                                    <span class="xb-item--cart-icon"><img src="{{asset('assets/img/icon/bag.svg')}}" alt="" loading="lazy"></span>
                                                    <span class="xb-item--cart">add to cart</span>
                                                </a>
                                            @elseif($item->stock == 0)
                                                <a href="#" class="xb-item--cart-btn disabled" style="color: lightgrey" >
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

    <!-- footer strt -->
        @include('partials/footer')
    <!-- footer end -->


</div>


<script src={{asset("assets/js/jquery.min.js")}}></script>
<!-- jquery include -->
<script src={{asset("assets/js/jquery-3.7.1.min.js")}}></script>
<script src={{asset("assets/js/bootstrap.bundle.min.js")}}></script>
<script src={{asset("assets/js/swiper.min.js")}}></script>
<script src="{{asset("assets/js/wow.min.js")}}"></script>
<script src={{asset("assets/js/jquery.magnific-popup.min.js")}}></script>
<script src={{asset("assets/js/touchspin.js")}}></script>
<script src={{asset("assets/js/jquery-ui.min.js")}}></script>
<script src={{asset("assets/js/jquery.inview.min.js")}}></script>
<script src={{asset("assets/js/jquery.easing.js")}}></script>
<script src={{asset("assets/js/scrollspy.js")}}></script>
<script src={{asset("assets/js/main.js")}}></script>

<!--minicart-->
<script>
    {{--function fetchCart() {--}}
    {{--    $.ajax({--}}
    {{--        url: '{{ route('api.cart.get') }}',--}}
    {{--        method: 'GET',--}}
    {{--        success: function(response) {--}}
    {{--            console.log("response", response.items); // Check response structure--}}

    {{--            if (!response.items || !Array.isArray(response.items)) {--}}
    {{--                console.error("Invalid response structure");--}}
    {{--                return;--}}
    {{--            }--}}

    {{--            $('.header-mini-cart').empty(); // Clear previous items--}}
    {{--            let total = 0;--}}

    {{--            if (response.items.length === 0) {--}}
    {{--                $('.header-mini-cart').append('<p>Your cart is empty.</p>');--}}
    {{--            } else {--}}
    {{--                response.items.forEach(item => {--}}
    {{--                    if (!item.product || !item.product.price || !item.product.name) {--}}
    {{--                        console.error("Invalid item structure");--}}
    {{--                        return;--}}
    {{--                    }--}}

    {{--                    const discount = item.product.discount || 0; // Get discount percentage--}}
    {{--                    const price = item.product.price;--}}
    {{--                    const discountedPrice = discount ? price * (1 - (discount / 100)) : price; // Apply discount--}}
    {{--                    const itemTotal = discountedPrice * item.quantity; // Calculate total for this item--}}

    {{--                    $('.header-mini-cart').append(`--}}
    {{--                        <div class="woocommerce-mini-cart-item d-flex align-items-center" style="padding: 10px;">--}}
    {{--                            <div class="mini-cart-img" style="margin-right: 10px;">--}}
    {{--                                <img src="{{asset('${item.product_image}')}}" alt="${item.product.name}" style="width: 50px; height: 50px; object-fit: cover;">--}}
    {{--                            </div>--}}
    {{--                            <div class="mini-cart-content" style="flex-grow: 1;">--}}
    {{--                                <h4 class="product-title" style="margin: 0; font-size: 14px;">--}}
    {{--                                    <a href="shop-details.html" style="text-decoration: none; color: #000;">${item.product.name}</a>--}}
    {{--                                </h4>--}}
    {{--                                <div class="mini-cart-price" style="margin-top: 5px;">--}}
    {{--                                    ${item.quantity} ×--}}
    {{--                                    <span class="woocommerce-Price-amount amount" style="color: red;">$${discountedPrice.toFixed(2)}</span>--}}

    {{--                                </div>--}}
    {{--                            </div>--}}
    {{--                            <div class="remove-button" style="margin-left: auto;">--}}
    {{--                                <a href="#" class="remove remove_from_cart_button" data-product_id="${item.product.id}" style="display: inline-block; width: 20px; height: 20px; border-radius: 50%; background-color: lightgrey; text-align: center; line-height: 20px; color: red; font-size: 14px;">×</a>--}}
    {{--                            </div>--}}
    {{--                        </div>--}}
    {{--                    `);--}}

    {{--                    total += itemTotal; // Sum the total amount--}}
    {{--                });--}}

    {{--                $('.header-mini-cart').append(`--}}
    {{--                    <p class="woocommerce-mini-cart__total">--}}
    {{--                        <strong>Subtotal:</strong>--}}
    {{--                        <span class="woocommerce-Price-amount">$${total.toFixed(2)}</span>--}}
    {{--                    </p>--}}
    {{--                    <p class="checkout-link">--}}
    {{--                        <a href="/viewCart" class="button wc-forward">View cart</a>--}}
    {{--                        <a href="/Checkout" class="button checkout wc-forward">Checkout</a>--}}
    {{--                    </p>--}}
    {{--                `);--}}
    {{--                $('.header-mini-cart').show();--}}
    {{--            }--}}
    {{--        },--}}
    {{--        error: function(xhr, status, error) {--}}
    {{--            console.error('AJAX error:', error);--}}
    {{--        }--}}
    {{--    });--}}
    {{--}--}}

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
                // alert(response.message);
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





<!--add to cart-->
<script>

    document.addEventListener('DOMContentLoaded', function() {
        const optionLinks = document.querySelectorAll('.option-value');
        const selectedOptionsInput = document.getElementById('selected_options');
        const quantityInput = document.getElementById('quantity');
        const form = document.getElementById('add-to-cart-form');
        const addToCartButton = form.querySelector('.add-to-cart');
        let selectedOptions = {};
        let totalOptions = new Set();

        // Show subscription modal on page load with delay
        setTimeout(function() {
            checkAndShowSubscriptionModal();
        }, 2000); // Show modal after 2 seconds


        optionLinks.forEach(link => {
            totalOptions.add(link.dataset.optionName);

            link.addEventListener('click', function(event) {
                event.preventDefault();
                const optionName = this.dataset.optionName;
                const optionValue = this.dataset.optionValue;

                // Toggle selection
                if (selectedOptions[optionName] === optionValue) {
                    delete selectedOptions[optionName];
                    this.classList.remove('selected');
                    this.style.color = ''; // Reset text color
                } else {
                    optionLinks.forEach(otherLink => {
                        if (otherLink.dataset.optionName === optionName) {
                            otherLink.classList.remove('selected');
                            otherLink.style.color = ''; // Reset text color
                        }
                    });
                    selectedOptions[optionName] = optionValue;
                    this.classList.add('selected');
                    this.style.color = 'white'; // Change text color to white
                }

                // Update the hidden input value
                selectedOptionsInput.value = JSON.stringify(selectedOptions);
                addToCartButton.disabled = false; // Enable the add to cart button
            });
        });

        // Handle add to cart form submission (simplified)
        $('#add-to-cart-form').submit(function (e) {
            e.preventDefault();
            processAddToCart();
        });

        // Function to check if subscription modal should be shown (for page load)
        function checkAndShowSubscriptionModal() {
            $.ajax({
                url: '{{ route('subscription.check.modal') }}',
                method: 'GET',
                success: function(response) {
                    if (response.show_modal) {
                        showSubscriptionModal();
                    }
                },
                error: function() {
                    console.log('Failed to check subscription modal status');
                }
            });
        }

        // Function to show subscription modal (simplified for page load)
        function showSubscriptionModal() {
            $('#subscriptionModal').fadeIn();

            // Handle modal close events
            const closeEvents = ['#closeModal', '#cancelSubscription'];
            closeEvents.forEach(selector => {
                $(selector).off('click').on('click', function() {
                    handleSubscriptionCancel();
                });
            });

            // Handle subscription form submission
            $('#subscriptionForm').off('submit').on('submit', function(e) {
                e.preventDefault();
                handleSubscriptionSubmit();
            });

            // Close modal when clicking outside
            $('.subscription-modal-overlay').off('click').on('click', function(e) {
                if (e.target === this) {
                    handleSubscriptionCancel();
                }
            });
        }

        // Handle subscription form submission (simplified)
        function handleSubscriptionSubmit() {
            const email = $('#subscriptionEmail').val();
            const submitButton = $('.btn-subscribe');
            const originalText = submitButton.text();

            submitButton.text('Processing...').prop('disabled', true);

            $.ajax({
                url: '{{ route('subscription.subscribe') }}',
                method: 'POST',
                data: {
                    email: email,
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    const messageDiv = $('#subscriptionMessage');

                    if (response.success) {
                        messageDiv.html('<div style="color: green;">' + response.message + '</div>').show();
                        setTimeout(() => {
                            $('#subscriptionModal').fadeOut();
                        }, 2000);
                    } else {
                        // Show error message but keep the form open
                        messageDiv.html('<div style="color: red;">' + response.message + '</div>').show();
                        // Clear the email input for the user to try a different email
                        $('#subscriptionEmail').val('').focus();
                    }
                },
                error: function(xhr) {
                    const errorMessage = xhr.responseJSON?.message || 'An error occurred. Please try again.';
                    $('#subscriptionMessage').html('<div style="color: red;">' + errorMessage + '</div>').show();
                },
                complete: function() {
                    submitButton.text(originalText).prop('disabled', false);
                }
            });
        }

        // Handle subscription cancellation (simplified)
        function handleSubscriptionCancel() {
            $.ajax({
                url: '{{ route('subscription.cancel') }}',
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                complete: function() {
                    $('#subscriptionModal').fadeOut();
                }
            });
        }

        // Function to process add to cart (simplified, no subscription check needed)
        function processAddToCart() {
            var formData = new FormData(document.getElementById('add-to-cart-form'));

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: $('#add-to-cart-form').attr('action'),
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#successAlert').text(response.message).fadeIn();
                    setTimeout(function () {
                        $('#successAlert').fadeOut();
                    }, 3000);

                    fetchCart();
                    $('.header-mini-cart').addClass('visible');
                    setTimeout(function () {
                        $('.header-mini-cart').removeClass('visible');
                    }, 5000);
                },
                error: function (xhr) {
                    var errorMessage = xhr.responseJSON.message || 'Please select an option before adding item to cart.';
                    $('#errorAlert').text(errorMessage).fadeIn();
                    setTimeout(function () {
                        $('#errorAlert').fadeOut();
                    }, 3000);
                }
            });
        }

        // Quantity validation
        quantityInput.addEventListener('change', function() {
            const quantity = parseInt(this.value, 10);
            if (isNaN(quantity) || quantity < 1) {
                this.value = 1;
            }
        });
    });

    // Cart management functions remain the same
    $(document).ready(function() {
        $('.header-shop-cart a').on('click', function() {
            $('.header-mini-cart').toggle();
        });
        fetchCart();
    });

    function fetchCart() {
        $.ajax({
            url: '{{ route('api.cart.get') }}',
            method: 'GET',
            success: function(response) {
                console.log("Fetching cart items", response.items); // Check response structure

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
                                <img src="{{asset('${item.product_image}')}}" alt="${item.product.name}" style="width: 50px; height: 50px; object-fit: cover;" loading="lazy">
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

                // Ensure the mini cart is shown
                $('.header-mini-cart').show();
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error);
            }
        });
    }


//reviews

    document.getElementById('reviewForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent form from submitting normally

    var formData = new FormData(this);

    fetch('{{ route("product.createReview", $product->id) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            var messageElement = document.getElementById('successMessage');
            messageElement.textContent = data.message; // Update with the success message
            messageElement.style.display = 'block'; // Make the message visible
            document.getElementById('reviewForm').reset(); // Optionally reset the form

            // Add the new review to the reviews section without reloading the page
            addNewReview(data.review);
        } else {
            alert('An error occurred. Please try again.'); // Show an error message if something went wrong
        }
    })
    .catch(error => console.error('Error:', error));
});

function addNewReview(review) {
    const reviewsSection = document.getElementById('reviewsSection');

    // Create a new review element
    const reviewElement = document.createElement('div');
    reviewElement.className = 'client-rv';
    reviewElement.innerHTML = `

            @if($agent->isMobile())
                                                        <div>
                                                            <img src="{{ asset('assets/img/icon/profile.png') }}" alt="Client Avatar" style="height: 60px;width:60px" loading="lazy">
                                                        </div>
                                                        @else
                                                        <div class="client-pic">
                                                        <img src="{{ asset('assets/img/icon/profile.png')}}" alt="Client Avatar" style="width:100px;height:60px" loading="lazy">
                                                    </div>
                                                        @endif

        <div class="details">
            <div class="name-rating-time" style="display: flex; flex-direction: column;">
                <div class="name-rating" style="display: flex; align-items: center;">
                    <h4 style="font-weight: bold; color: black; margin: 0;">${review.name}</h4>
                </div>
                <div class="review-body" style="margin-top: 5px;">
                    <p>${review.message}</p>
                </div>
            </div>
        </div>
    `;

    // Append the new review to the reviews section
    reviewsSection.prepend(reviewElement); // Add to the top of the reviews section
}

let stars = document.getElementsByClassName("star");
let output = document.getElementById("output");

// Function to update the rating
function gfg(n) {
    remove(); // Reset previous styling
    for (let i = 0; i < n; i++) {
        stars[i].style.color = "red"; // Fill color red
    }
    output.innerText = "Rating is: " + n + "/5";
}

// Reset function
function remove() {
    for (let i = 0; i < stars.length; i++) {
        stars[i].style.color = "#ccc"; // Reset to default color
    }
}

</script>
</body>



</html>
