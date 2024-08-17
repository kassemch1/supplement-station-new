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

    <link rel="shortcut icon" href={{ asset('assets/img/logo/preloader2.png')}} type="images/x-icon"/>

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
        <section class="breadcrumb position-bottom bg_img" data-background="{{ asset('assets/img/bg/shop-cart-banner.png')}}">
            <div class="container">
                <div class="breadcrumb__content text-center">
                    <h2 class="breadcrumb__title">Shop Details</h2>
                    <ul class="breadcrumb__list clearfix">
                        <li class="breadcrumb-item"><a href="index.html">Purefit</a></li>
                        <li class="breadcrumb-item">Shop Details</li>
                    </ul>
                </div>
            </div>
        </section>
        <!-- breadcrumb end -->

        <!-- shop single start -->
        <section class="shop-single-section pt-115 pb-385">
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
                                                <img src="{{ asset($image->url) }}" alt="{{ $products->name }} image {{ $index + 1 }}"  style="width: auto;display: block;object-fit: cover;max-width: 100%; /* Ensure it doesn't exceed its container width */
    height: 500px; /* Auto height to ensure responsive scaling */">
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
                                                <img src="{{ asset($image->url) }}" alt="{{ $products->name }} thumbnail {{ $index + 1 }}" height="100" width="100">
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
                    <p>{{ $product->description ?? 'Product description' }}</p>
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
                                    <p>Travelling salesman and above it there hung a picture that he had recently cut out of an illustrated magazine and housed in a nice, gilded frame. It showed a lady fitted out with a fur hat and fur boa who sat upright, raising a heavy fur muff that covered the whole of her lower arm towards the viewer</p>
                                    <p> waved about helplessly as he looked. "What's happened to me?" he thought. It wasn't a dream. His room, a proper human room although a little too small, lay peacefully between its four familiar wallstrated magazine and housed in a nice, gilded frame. It showed a lady fitted out with a fur hat and fur boa who sat upright, raising a heavy fur muff that covered the whole of her lower arm towards the viewer. Gregor then turned to look out the window at the dull weather</p>
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
                                                    <div class="client-pic">
                                                        <img src="https://static-00.iconduck.com/assets.00/profile-circle-icon-2048x2048-cqe5466q.png" alt="Client Avatar">
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
                                                <form id="reviewForm">
                                                    @csrf <!-- CSRF token for security -->
                                                    <div>
                                                        <input type="text" class="form-control" name="name" placeholder="Name *" required>
                                                    </div>
                                                    <div>
                                                        <input type="text" class="form-control" name="phoneNumber" placeholder="Phone *" required>
                                                    </div>
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
                                                        <img src="{{ asset($item->images->first()->url) }}" alt="img" style="max-height: 120px">
                                                    @else
                                                        No image available
                                                    @endif
                                                </a>
                                            @else
                                                <div class="no-stock">
                                                    <!-- Add image here -->
                                                    @if($item->images->isNotEmpty())
                                                        <img src="{{ asset($item->images->first()->url) }}" alt="img" style="max-height: 120px">
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
                                                    <span class="xb-item--cart-icon"><img src="{{asset('assets/img/icon/bag.svg')}}" alt=""></span>
                                                    <span class="xb-item--cart">add to cart</span>
                                                </a>
                                            @elseif($item->stock == 0)
                                                <a href="#" class="xb-item--cart-btn disabled" style="color: lightgrey" >
                                                    <span class="xb-item--cart-icon"><img src="{{asset('assets/img/icon/bag.svg')}}" alt=""></span>
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





<!--add to cart-->
<script>
    // document.addEventListener('DOMContentLoaded', function() {
    //     const optionLinks = document.querySelectorAll('.option-value');
    //     const selectedOptionsInput = document.getElementById('selected_options');
    //     const quantityInput = document.getElementById('quantity');
    //     const form = document.getElementById('add-to-cart-form');
    //     const addToCartButton = form.querySelector('.add-to-cart');
    //     let selectedOptions = {};
    //     let totalOptions = new Set();
    //
    //     optionLinks.forEach(link => {
    //         totalOptions.add(link.dataset.optionName);
    //
    //         link.addEventListener('click', function(event) {
    //             event.preventDefault();
    //             const optionName = this.dataset.optionName;
    //             const optionValue = this.dataset.optionValue;
    //
    //             // Toggle selection
    //             if (selectedOptions[optionName] === optionValue) {
    //                 // Option is already selected, remove it
    //                 delete selectedOptions[optionName];
    //                 this.classList.remove('selected');
    //                 this.style.color = ''; // Reset text color
    //             } else {
    //                 // Option is not selected, add it and remove any existing selection for the same option name
    //                 optionLinks.forEach(otherLink => {
    //                     if (otherLink.dataset.optionName === optionName) {
    //                         otherLink.classList.remove('selected');
    //                         otherLink.style.color = ''; // Reset text color
    //                     }
    //                 });
    //                 selectedOptions[optionName] = optionValue;
    //                 this.classList.add('selected');
    //                 this.style.color = 'white'; // Change text color to white
    //             }
    //
    //             // Update the hidden input value
    //             selectedOptionsInput.value = JSON.stringify(selectedOptions);
    //
    //             // Enable the add to cart button
    //             addToCartButton.disabled = false;
    //         });
    //     });
    //
    //     // Handle form submission with AJAX
    //     $('#add-to-cart-form').submit(function (e) {
    //         e.preventDefault(); // Prevent default form submission
    //
    //         // Check if there are selected options
    //         if (Object.keys(selectedOptions).length === 0) {
    //             selectedOptionsInput.value = JSON.stringify({});
    //         } else {
    //             selectedOptionsInput.value = JSON.stringify(selectedOptions);
    //         }
    //
    //         // Debugging logs
    //         console.log('Selected Options:', selectedOptions);
    //         console.log('Selected Options Input Value:', selectedOptionsInput.value);
    //
    //         // Serialize form data
    //         var formData = new FormData(this);
    //
    //         // Send AJAX request
    //         $.ajax({
    //             headers: {
    //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //             },
    //             type: 'POST',
    //             url: $(this).attr('action'), // Use form's action attribute as URL
    //             data: formData,
    //             processData: false,
    //             contentType: false,
    //             success: function (response) {
    //                 // Show success alert
    //                 $('#successAlert').text(response.message).fadeIn();
    //                 setTimeout(function () {
    //                     $('#successAlert').fadeOut();
    //                 }, 3000);
    //             },
    //             error: function (xhr) {
    //                 // Show error alert
    //                 var errorMessage = xhr.responseJSON.message || 'There was an error adding the item to the cart.';
    //                 $('#errorAlert').text(errorMessage).fadeIn();
    //                 setTimeout(function () {
    //                     $('#errorAlert').fadeOut();
    //                 }, 3000);
    //             }
    //         });
    //     });
    //
    //     // Optional: Validate or update quantity based on specific rules
    //     quantityInput.addEventListener('change', function() {
    //         const quantity = parseInt(this.value, 10);
    //         if (isNaN(quantity) || quantity < 1) {
    //             this.value = 1; // Default to 1 if invalid
    //         }
    //     });
    // });

    document.addEventListener('DOMContentLoaded', function() {
        const optionLinks = document.querySelectorAll('.option-value');
        const selectedOptionsInput = document.getElementById('selected_options');
        const quantityInput = document.getElementById('quantity');
        const form = document.getElementById('add-to-cart-form');
        const addToCartButton = form.querySelector('.add-to-cart');
        let selectedOptions = {};
        let totalOptions = new Set();

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

        // Handle form submission with AJAX
        $('#add-to-cart-form').submit(function (e) {
            e.preventDefault(); // Prevent default form submission

            // Serialize form data
            var formData = new FormData(this);

            // Send AJAX request
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'POST',
                url: $(this).attr('action'), // Use form's action attribute as URL
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#successAlert').text(response.message).fadeIn();
                    setTimeout(function () {
                        $('#successAlert').fadeOut();
                    }, 3000);

                    fetchCart(); // Refresh cart content

                    // Open the mini cart
                    $('.header-mini-cart').addClass('visible'); // Add the class to show the mini cart

                    // Optionally, remove the class after some time to close it
                    setTimeout(function () {
                        $('.header-mini-cart').removeClass('visible');
                    }, 5000); // Close after 3 seconds
                },
                error: function (xhr) {
                    var errorMessage = xhr.responseJSON.message || 'There was an error adding the item to the cart.';
                    $('#errorAlert').text(errorMessage).fadeIn();
                    setTimeout(function () {
                        $('#errorAlert').fadeOut();
                    }, 3000);
                }
            });
        });

        // Optional: Validate or update quantity based on specific rules
        quantityInput.addEventListener('change', function() {
            const quantity = parseInt(this.value, 10);
            if (isNaN(quantity) || quantity < 1) {
                this.value = 1; // Default to 1 if invalid
            }
        });
    });

    // Fetch cart items on page load
    $(document).ready(function() {
        $('.header-shop-cart a').on('click', function() {
            $('.header-mini-cart').toggle(); // Toggle visibility on click
        });
        fetchCart(); // Fetch cart items
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
                                <img src="{{asset('${item.product_image}')}}" alt="${item.product.name}" style="width: 50px; height: 50px; object-fit: cover;">
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
        <div class="client-pic">
            <img src="https://static-00.iconduck.com/assets.00/profile-circle-icon-2048x2048-cqe5466q.png" alt="Client Avatar">
        </div>
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
