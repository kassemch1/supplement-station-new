
<!doctype html>
<html lang="zxx">

<head>

    <!--========= Required meta tags =========-->
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Home</title>

    <link rel="shortcut icon" href="assets/img/favicon.png" type="images/x-icon"/>

    <!-- css include -->
    <link rel="stylesheet" href={{asset("assets/css/bootstrap.min.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/fontawesome.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/animate.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/swiper.min.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/jquery-ui.min.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/magnific-popup.css")}}>
    <link rel="stylesheet" href={{asset("assets/css/main.css")}}>
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



    <div class="body-overlay"></div>

    <!-- main area start  -->
    <main>
        <!-- hero start -->
        <section class="hero hero-style-one bg_img hero-height d-flex align-items-center" data-background="assets/img/bg/hero_bg.jpg">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="hero__content sec-title">
                            <span class="sub-title wow fadeInUp" data-wow-duration=".6s">100% PREMIUM QUALITY</span>
                            <h1 class="title mb-25 wow fadeInUp" data-wow-delay="150ms" data-wow-duration=".6s">Today Elevate Your Energy Levels at Supplement Station</h1>
                            <div class="hero__action ul_li wow fadeInUp" data-wow-delay="300ms" data-wow-duration=".6s">
                                @if($product)
                                    <a class="thm-btn mr-45 mt-30" href="{{ route('products.show', ['id'=>$banner->product_id]) }}">BUY NOW</a>
                                @endif
                                <div class="hero__cta ul_li mt-30">
                                    <span class="icon">
                                        <img src="assets/img/icon/call.svg" alt="">
                                    </span>
                                    <div class="info">
                                        <span>CONTACT US DAILY</span>
                                        <h4>+961 81-823-038</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="hero__product text-md-end">
                            @if($product)
                                <img class="wow fadeInRight" data-wow-delay="300ms" data-wow-duration=".6s" src="{{asset("$banner->image")}}" alt="" height="500" width="500">
                            @endif
                        </div>
                    </div>

                </div>
            </div>
            <div class="hero-shape">
                <img src="assets/img/bg/hero_shape.png" alt="">
            </div>
        </section>
        <!-- hero end -->

        <div class="bg_img position-botttom bottom--105 pb-70" data-background="assets/img/bg/pp_bg.png">
         <!-- popular product start -->
<section class="popular-product pt-120 pb-120">
    <div class="container">
        <div class="sec-title text-center mb-30">
            <span class="sub-title">Shop</span>
            <h2 class="title">Our Popular Products</h2>
        </div>
        <div class="row">
            <div class="col-lg-8 pb-col-8">
                <div class="row g-20">
                    @forelse ($product as $productItem)
                        @php
                            $ratingCount = $productItem->reviews()->count();
                            $averageRating = $ratingCount > 0 ? $productItem->reviews()->avg('rating') : 5;
                        @endphp
                    <div class="col-lg-6 col-md-6 mt-20">
                        <div class="popular-product-item ul_li">
                            <div class="xb-item--img">
                                <a href="{{ route('products.show', $productItem->id) }}">
                                    <img src="{{ asset($productItem->images->first()->url) }}" alt="{{ $productItem->name }}" style="width: 100%; height: auto; object-fit: cover;">
                                </a>
                            </div>
                            <div class="xb-item--holder">
                                <h3 class="xb-item--title"><a href="{{ route('products.show', $productItem->id) }}">{{ $productItem->name }}</a></h3>
                                <div class="xb-item--rating-inner ul_li">
                                    <ul class="xb-item--rating ul_li">
{{--                                        @if($averageRating>0)--}}
                                        @for ($i = 0; $i < 5; $i++)
                                            <i class="fas fa-star{{ $i < $averageRating ? '' : '-o' }}"></i>
                                        @endfor
{{--                                        @endif--}}
                                            <span>({{ $ratingCount }} Customer review{{ $ratingCount != 1 ? 's' : '' }})</span>

                                    </ul>

                                </div>
                                <div class="xb-item--action ul_li_between">
                                    <h4 class="xb-item--price">${{ number_format($productItem->price, 2) }}</h4>
                                    <a class="xb-item--cart" href="{{ route('products.show', $productItem->id) }}">
                                        <img src="assets/img/icon/bag.svg" alt="">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="col-12">
                        <p>No popular products found.</p>
                    </div>
                    @endforelse
                </div>
            </div>
            <div class="col-lg-4 pb-col-4">
                <div class="popular-product__img mt-20">
                    <img src="assets/img/bg/pp_img.jpg" alt="">
                </div>
            </div>
        </div>
    </div>
</section>
<!-- popular product end -->



            <!-- about start -->
            <section class="about pb-120">
                <div class="container">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="sec-title mb-30">
                                <span class="sub-title">about Supplement Station</span>
                                <h2 class="title">At Supplement Station  Your health journey begins!</h2>
                            </div>
                            <div class="about-experience">
                                <span>since</span>
                                <h2>1998</h2>
                            </div>
                        </div>
                        <div class="col-lg-6 mt-40">
                            <div class="about-content">
                                <p>At Supplement Station, we're dedicated to helping you  on a transformative health journey. Our mission is to provide you with the highest quality supplements, backed by science and crafted with care. Here's what you can expect from us.</p>
                                <ul class="about-list ul_li mt-10">
                                    <li><img src="assets/img/icon/check.svg" alt="">Premium Quality Products</li>
                                    <li><img src="assets/img/icon/check.svg" alt="">Clear Information</li>
                                    <li><img src="assets/img/icon/check.svg" alt="">Customized Training Plans</li>
                                    <li><img src="assets/img/icon/check.svg" alt="">Expert Guidance</li>
                                    <li><img src="assets/img/icon/check.svg" alt="">Increased Energy</li>
                                    <li><img src="assets/img/icon/check.svg" alt="">Friendly Service</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- about end -->

        </div>

        <!-- feature start -->
        <section id="features" class="feature bg_img pt-200 pb-120" data-background="assets/img/bg/feature_bg.jpg">
            <div class="container">
                <div class="row align-items-center mt-none-30">
                    <div class="col-lg-4 col-md-6 mt-30">
                        <ul class="feature-list list-unstyled">
                            <li>
                                <span class="xb-item--icon">
                                    <img src="assets/img/icon/ft_01.svg" alt="">
                                </span>
                                <div class="xb-item--holder">
                                    <h3 class="xb-item--title">Supports Optimal Performance</h3>
                                    <p class="xb-item--desc">High-quality supplements can enhance your physical performance, helping you achieve your fitness goals more effectively.</p>
                                </div>
                            </li>
                            <li>
                                <span class="xb-item--icon">
                                    <img src="assets/img/icon/ft_02.svg" alt="">
                                </span>
                                <div class="xb-item--holder">
                                    <h3 class="xb-item--title">Boosts Recovery <br> Time</h3>
                                    <p class="xb-item--desc">Supplements with ingredients like protein, amino acids, and antioxidants can aid in quicker recovery after intense workouts or physical exertion.</p>
                                </div>
                            </li>
                            <li>
                                <span class="xb-item--icon">
                                    <img src="assets/img/icon/ft_03.svg" alt="">
                                </span>
                                <div class="xb-item--holder">
                                    <h3 class="xb-item--title">Promotes Muscle <br> Growth</h3>
                                    <p class="xb-item--desc">Essential supplements like creatine, BCAAs, and whey protein can support muscle development and strength gains, contributing to overall fitness progress.</p>
                                </div>
                            </li>
                            <li>
                                <span class="xb-item--icon">
                                    <img src="assets/img/icon/ft_04.svg" alt="">
                                </span>
                                <div class="xb-item--holder">
                                    <h3 class="xb-item--title">Enhances Energy <br> Levels</h3>
                                    <p class="xb-item--desc">Energy-boosting supplements can help improve endurance and reduce fatigue, allowing you to stay active and motivated throughout the day.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="col-lg-5 offset-lg-3 col-md-6 mt-30">
                        <div class="feature-content">
                            <div class="sec-title sec-title--big sec-title--white">
                                <span class="sub-title">100% PREMIUM QUALITY</span>
                                <h2 class="title mb-25">Maximize Your Fitness Potential</h2>
                                <p>At Supplement Station, we're dedicated to helping you  on a health journey. Our mission is to provide 100% high-quality supplements, personalized support, and expert guidance to ensure you achieve your wellness goals..</p>
                                <a class="thm-btn mt-45" href="#!">LEARN MORE</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- feature end -->

  <!-- product start -->
<section id="shop" class="product bg_img pt-120 pb-130" data-background="assets/img/bg/shop_bg.jpg">
    <div class="container">
        <div class="sec-title text-center mb-55">
            <span class="sub-title">products</span>
            <h2 class="title">best selling products</h2>
        </div>
        <div class="product-slider swiper-container">
            <div class="swiper-wrapper">
                @forelse ($product as $product)
                    <div class="swiper-slide product-item text-center">
                        <div class="xb-item--img">
                            <a href="{{ route('products.show', $product->id) }}">
                                <!-- Add image here -->
                                @if($product->images->isNotEmpty())
                                    <img src="{{ asset($product->images->first()->url) }}" alt="img">
                                @else
                                    No image available
                                @endif
                            </a>
                        </div>
                        <div class="xb-item--holder">
                            <h3 class="xb-item--title">
                                <a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a>
                            </h3>
                            @php
                                $ratingCount = $product->reviews()->count();
                                $averageRating = $ratingCount > 0 ? $product->reviews()->avg('rating') : 5;
                            @endphp
                            <div class="xb-item--rating-inner ul_li_center">
                                <ul class="xb-item--rating ul_li">
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="fas fa-star{{ $i < $averageRating ? '' : '-o' }}"></i>
                                    @endfor

                                </ul>
                            </div>
                        </div>
                        <div class="xb-item--action ul_li mt-20">
                            <span class="xb-item--price">${{ number_format($product->price, 2) }}</span>
                            <a href="{{ route('products.show', $product->id) }}">
                                <span class="xb-item--cart-icon"><img src="assets/img/icon/bag.svg" alt=""></span>
                                <span class="xb-item--cart">add to cart</span>
                            </a>
                        </div>
                    </div>
                @empty
                    <p>No products available.</p>
                @endforelse
            </div>
        </div>
    </div>
</section>
<!-- product end -->

        <!-- testimonial start -->
        <section class="testimonial bg_img pt-120 pb-115" data-background="assets/img/bg/tm_bg.jpg">
            <div class="container">
                <div class="sec-title sec-title--white text-center mb-60">
                    <span class="sub-title">OVERALL RATING</span>
                    <h2 class="title">CUSTOMER REVIEWS</h2>
                </div>
                <div class="testimonial-slider swiper-container">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide xb-testimonial">
                            <div class="xb-item--author ul_li">
                                <div class="xb-item--avatar">
                                    <img src="assets/img/avatar/tst_01.png" alt="">
                                    <div class="xb-item--quote">
                                        <img src="assets/img/icon/quote.svg" alt="">
                                    </div>
                                </div>
                                <div class="xb-item--holder">
                                    <h3 class="xb-item--name">Richard Thomas</h3>
                                    <span class="xb-item--date">October 17, 2024</span>
                                </div>
                            </div>
                            <div class="xb-item--desc mt-35">
                                "I've been using Purefit for a few months, and it has significantly boosted my energy levels. It's a game-changer for my daily routine. Thank you, Purefit!"
                            </div>
                        </div>
                        <div class="swiper-slide xb-testimonial">
                            <div class="xb-item--author ul_li">
                                <div class="xb-item--avatar">
                                    <img src="assets/img/avatar/tst_02.png" alt="">
                                    <div class="xb-item--quote">
                                        <img src="assets/img/icon/quote.svg" alt="">
                                    </div>
                                </div>
                                <div class="xb-item--holder">
                                    <h3 class="xb-item--name">Richard Thomas</h3>
                                    <span class="xb-item--date">October 17, 2024</span>
                                </div>
                            </div>
                            <div class="xb-item--desc mt-35">
                                "Since I started using purefit, my energy levels have skyrocketed. I'm more alert and focused, and it's transformed my daily productivity. Highly recommended!"
                            </div>
                        </div>
                        <div class="swiper-slide xb-testimonial">
                            <div class="xb-item--author ul_li">
                                <div class="xb-item--avatar">
                                    <img src="assets/img/avatar/tst_03.png" alt="">
                                    <div class="xb-item--quote">
                                        <img src="assets/img/icon/quote.svg" alt="">
                                    </div>
                                </div>
                                <div class="xb-item--holder">
                                    <h3 class="xb-item--name">Richard Thomas</h3>
                                    <span class="xb-item--date">October 17, 2024</span>
                                </div>
                            </div>
                            <div class="xb-item--desc mt-35">
                                "As an active person, I've had joint discomfort. Purefit has made a remarkable difference, allowing me to enjoy daily activities. Thank you, Purefit!"
                            </div>
                        </div>
                        <div class="swiper-slide xb-testimonial">
                            <div class="xb-item--author ul_li">
                                <div class="xb-item--avatar">
                                    <img src="assets/img/avatar/tst_01.png" alt="">
                                    <div class="xb-item--quote">
                                        <img src="assets/img/icon/quote.svg" alt="">
                                    </div>
                                </div>
                                <div class="xb-item--holder">
                                    <h3 class="xb-item--name">Richard Thomas</h3>
                                    <span class="xb-item--date">October 17, 2024</span>
                                </div>
                            </div>
                            <div class="xb-item--desc mt-35">
                                "I've been using Purefit for a few months, and it has significantly boosted my energy levels. It's a game-changer for my daily routine. Thank you, Purefit!"
                            </div>
                        </div>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </section>
        <!-- testimonial end -->

        <!-- pricing start -->
        <section id="pricing" class="pricing pt-120 pb-130" data-bg-color="#fff">

            <div class="container">
                <div class="sec-title text-center mb-55">
                    <span class="sub-title">plans</span>
                    <h2 class="title">flexible plans</h2>
                </div>
{{--                <div class="swiper-wrapper">--}}
                <div class="row justify-content-center mt-none-30">

                        @foreach($planes as $plane)
                        @if($plane->id%2==1)
                    <div class="col-lg-4 col-md-6 mt-30">


                        <div class="xb-pricing">
                            <div class="xb-item--head pos-rel">
                                <h4 class="xb-item--title">{{$plane->type}}</h4>
                                <h2 class="xb-item--price">${{$plane->price}}<span>Per Plan</span>  </h2>
                                <h3 class="xb-item--price-sub">(${{$plane->price}} total)</h3>
                                <div class="xb-item--img" data-background="assets/img/pricing/img_01.png"></div>
                                <div class="xb-item--shape" data-background="assets/img/shape/pricing_shape.png"></div>
                            </div>
                            <div class="xb-item--body">
                                <ul class="xb-item--list list-unstyled">
                                    @if($plane->point1)
                                    <li><img src="assets/img/icon/p_check.svg" alt="">{{$plane->point1}}</li>
                                    @endif

                                    @if($plane->point2)
                                    <li><img src="assets/img/icon/p_check.svg" alt="">{{$plane->point2}}</li>
                                        @endif
                                        @if($plane->point3)
                                            <li><img src="assets/img/icon/p_check.svg" alt="">{{$plane->point3}}</li>
                                        @endif
                                        @if($plane->point4)
                                            <li><img src="assets/img/icon/p_check.svg" alt="">{{$plane->point4}}</li>
                                        @endif
                                        @if($plane->point5)
                                            <li><img src="assets/img/icon/p_check.svg" alt="">{{$plane->point5}}</li>
                                        @endif
                                </ul>
                                <div class="xb-item--btn">
                                    <a class="thm-btn w-100 text-center mt-45" href="https://api.whatsapp.com/send?phone=81088266"><img src="assets/img/icon/bag.svg" alt="">LEARN MORE</a>
                                </div>
                            </div>
                        </div>


                    </div>
                        @elseif($plane->id%2==0)



                    <div class="col-lg-4 col-md-6 mt-30">
                        <div class="xb-pricing active">
                            <div class="xb-item--head pos-rel">
                                <h4 class="xb-item--title">{{$plane->type}}</h4>
                                <h2 class="xb-item--price">${{$plane->price}}<span>Per Plan</span></h2>
                                <h3 class="xb-item--price-sub">(${{$plane->price}} total)</h3>
                                <div class="xb-item--img" data-background="assets/img/pricing/img_02.png"></div>
                                <div class="xb-item--shape" data-background="assets/img/shape/pricing_shape2.png"></div>
                            </div>
                            <div class="xb-item--body">
                                <ul class="xb-item--list list-unstyled">
                                    @if($plane->point1)
                                        <li><img src="assets/img/icon/check_h.svg" alt="">{{$plane->point1}}</li>
                                    @endif
                                    @if($plane->point2)
                                        <li><img src="assets/img/icon/check_h.svg" alt="">{{$plane->point2}}</li>
                                    @endif
                                    @if($plane->point3)
                                        <li><img src="assets/img/icon/check_h.svg" alt="">{{$plane->point3}}</li>
                                    @endif
                                    @if($plane->point4)
                                        <li><img src="assets/img/icon/check_h.svg" alt="">{{$plane->point4}}</li>
                                    @endif
                                    @if($plane->point5)
                                        <li><img src="assets/img/icon/check_h.svg" alt="">{{$plane->point5}}</li>
                                    @endif
                                </ul>
                                <div class="xb-item--btn">
                                    <a class="thm-btn w-100 text-center mt-45" href="https://api.whatsapp.com/send?phone=81088266"><img src="assets/img/icon/bag.svg" alt="">LEARN MORE</a>
                                </div>
                            </div>
                        </div>
                    </div>
                        @endif
                            @endforeach
                </div>
{{--                </div>--}}
            </div>

        </section>
        <!-- pricing end -->

        <!-- faq start -->
        <section class="faq" data-bg-color="#fff">
            <div class="container">
                <div class="accordion-inner bg_img" data-background="assets/img/bg/faq_bg.jpg">
                    <div class="sec-title sec-title--white text-center mb-60">
                        <span class="sub-title">faq</span>
                        <h2 class="title">Frequently asked questions</h2>
                    </div>
                    <ul class="xb-accordion accordion_box clearfix">
                        @foreach($faqs as $faq)
                            <li class="accordion block">
                                <div class="acc-btn">
                                    {{$faq->question}}
                                    <span class="arrow"></span>
                                </div>
                                <div class="acc_body">
                                    <div class="content">
                                        <p>{{$faq->answer}}</p>

                                    </div>
                                </div>
                            </li>
                        @endforeach
{{--                        <li class="accordion block active-block">--}}
{{--                            <div class="acc-btn">--}}
{{--                                Are your supplements suitable for vegans or vegetarians?--}}
{{--                                <span class="arrow"></span>--}}
{{--                            </div>--}}
{{--                            <div class="acc_body current">--}}
{{--                                <div class="content">--}}
{{--                                    <p>When addressing whether your supplements are suitable for vegans or vegetarians, you can following points:</p>--}}
{{--                                    <ul>--}}
{{--                                        <li>Vegan-Friendly Ingredients</li>--}}
{{--                                        <li>No Animal-Derived Ingredients</li>--}}
{{--                                        <li>Alternative Capsules</li>--}}
{{--                                    </ul>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="accordion block">--}}
{{--                            <div class="acc-btn">--}}
{{--                                Can I take multiple supplements together?--}}
{{--                                <span class="arrow"></span>--}}
{{--                            </div>--}}
{{--                            <div class="acc_body">--}}
{{--                                <div class="content">--}}
{{--                                    <p>When addressing whether your supplements are suitable for vegans or vegetarians, you can following points:</p>--}}
{{--                                    <ul>--}}
{{--                                        <li>Vegan-Friendly Ingredients</li>--}}
{{--                                        <li>No Animal-Derived Ingredients</li>--}}
{{--                                        <li>Alternative Capsules</li>--}}
{{--                                    </ul>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="accordion block">--}}
{{--                            <div class="acc-btn">--}}
{{--                                Can I take supplements with medications or other dietary products?--}}
{{--                                <span class="arrow"></span>--}}
{{--                            </div>--}}
{{--                            <div class="acc_body">--}}
{{--                                <div class="content">--}}
{{--                                    <p>When addressing whether your supplements are suitable for vegans or vegetarians, you can following points:</p>--}}
{{--                                    <ul>--}}
{{--                                        <li>Vegan-Friendly Ingredients</li>--}}
{{--                                        <li>No Animal-Derived Ingredients</li>--}}
{{--                                        <li>Alternative Capsules</li>--}}
{{--                                    </ul>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="accordion block">--}}
{{--                            <div class="acc-btn">--}}
{{--                                What is the recommended daily dosage for your supplements?--}}
{{--                                <span class="arrow"></span>--}}
{{--                            </div>--}}
{{--                            <div class="acc_body">--}}
{{--                                <div class="content">--}}
{{--                                    <p>When addressing whether your supplements are suitable for vegans or vegetarians, you can following points:</p>--}}
{{--                                    <ul>--}}
{{--                                        <li>Vegan-Friendly Ingredients</li>--}}
{{--                                        <li>No Animal-Derived Ingredients</li>--}}
{{--                                        <li>Alternative Capsules</li>--}}
{{--                                    </ul>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </li>--}}
{{--                        <li class="accordion block">--}}
{{--                            <div class="acc-btn">--}}
{{--                                Are your supplements tested for quality and purity?--}}
{{--                                <span class="arrow"></span>--}}
{{--                            </div>--}}
{{--                            <div class="acc_body">--}}
{{--                                <div class="content">--}}
{{--                                    <p>When addressing whether your supplements are suitable for vegans or vegetarians, you can following points:</p>--}}
{{--                                    <ul>--}}
{{--                                        <li>Vegan-Friendly Ingredients</li>--}}
{{--                                        <li>No Animal-Derived Ingredients</li>--}}
{{--                                        <li>Alternative Capsules</li>--}}
{{--                                    </ul>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </li>--}}
                    </ul>
                </div>
            </div>
        </section>
        <!-- faq end -->

        <!-- brand start -->
        <section class="brand pt-110 md-pb-0 pb-90" data-bg-color="#fff">
            <div class="container">
                <div class="sec-title text-center mb-35">
                    <span class="sub-title">Perfect Brand is Featured on</span>
                </div>
                <div class="xb-swiper-sliders brand-slider">
                    <div class="xb-carousel-inner">
                        <div class="xb-swiper-container swiper-container">
                            <div class="xb-swiper-wrapper swiper-wrapper">
                                <div class="swiper-slide xb-swiper-slide">
                                    <a href="#!"><img src="assets/img/brand/img_01.png" alt=""></a>
                                </div>
                                <div class="swiper-slide xb-swiper-slide">
                                    <a href="#!"><img src="assets/img/brand/img_02.png" alt=""></a>
                                </div>
                                <div class="swiper-slide xb-swiper-slide">
                                    <a href="#!"><img src="assets/img/brand/img_03.png" alt=""></a>
                                </div>
                                <div class="swiper-slide xb-swiper-slide">
                                    <a href="#!"><img src="assets/img/brand/img_04.png" alt=""></a>
                                </div>
                                <div class="swiper-slide xb-swiper-slide">
                                    <a href="#!"><img src="assets/img/brand/img_05.png" alt=""></a>
                                </div>
                                <div class="swiper-slide xb-swiper-slide">
                                    <a href="#!"><img src="assets/img/brand/img_06.png" alt=""></a>
                                </div>
                                <div class="swiper-slide xb-swiper-slide">
                                    <a href="#!"><img src="assets/img/brand/img_01.png" alt=""></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- brand end -->

        <!-- blog start -->
{{--        <section id="blog" class="blog position-top blog-pb bg_img pt-180" data-background="assets/img/bg/blog_bg.jpg">--}}
{{--            <div class="container">--}}
{{--                <div class="ul_li_between align-items-end mb-25">--}}
{{--                    <div class="sec-title mb-30">--}}
{{--                        <span class="sub-title">blog</span>--}}
{{--                        <h2 class="title">latest blog</h2>--}}
{{--                    </div>--}}
{{--                    <a class="border-btn mb-30" href="blog.html">view all blog</a>--}}
{{--                </div>--}}
{{--                <div class="row mt-none-30 justify-content-center">--}}
{{--                    <div class="col-lg-4 col-md-6 mt-30">--}}
{{--                        <div class="xb-blog">--}}
{{--                            <div class="xb-item--img">--}}
{{--                                <a href="blog-single.html"><img src="assets/img/blog/img_01.jpg" alt=""></a>--}}
{{--                                <div class="xb-item--date">28 <br><span>sep</span></div>--}}
{{--                            </div>--}}
{{--                            <div class="xb-item--holder">--}}
{{--                                <span class="xb-item--author"><img src="assets/img/icon/user.svg" alt="">Steven Timothy</span>--}}
{{--                                <h3 class="xb-item--title border-effect"><a href="blog-single.html">The Power of Adaptogens Stress Relief and..</a></h3>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-lg-4 col-md-6 mt-30">--}}
{{--                        <div class="xb-blog">--}}
{{--                            <div class="xb-item--img">--}}
{{--                                <a href="blog-single.html"><img src="assets/img/blog/img_02.jpg" alt=""></a>--}}
{{--                                <div class="xb-item--date">28 <br><span>sep</span></div>--}}
{{--                            </div>--}}
{{--                            <div class="xb-item--holder">--}}
{{--                                <span class="xb-item--author"><img src="assets/img/icon/user.svg" alt="">Andrew Brian</span>--}}
{{--                                <h3 class="xb-item--title border-effect"><a href="blog-single.html">The Role of Supplements in Athletic Perf..</a></h3>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-lg-4 col-md-8 mt-30">--}}
{{--                        <div class="xb-blog">--}}
{{--                            <div class="xb-item--img">--}}
{{--                                <a href="blog-single.html"><img src="assets/img/blog/img_03.jpg" alt=""></a>--}}
{{--                                <div class="xb-item--date">28 <br><span>sep</span></div>--}}
{{--                            </div>--}}
{{--                            <div class="xb-item--holder">--}}
{{--                                <span class="xb-item--author"><img src="assets/img/icon/user.svg" alt="">Andrew Brian</span>--}}
{{--                                <h3 class="xb-item--title border-effect"><a href="blog-single.html">Discover the Benefits of Essential Nutrients..</a></h3>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </section>--}}
        <!-- blog end -->

    </main>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>
<br>

    <!-- main area end  -->

    <!-- footer strt -->
        @include('partials/footer')
    <!-- footer end -->


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

</body>



</html>
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
                        <a href="/viewCart" class="button wc-forward">View cart</a>
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










