{{--
    Layout of the store pages (home, shop, product, cart, checkout). A page sets:
      @section('title')        text before " | Supplement Station"
      @section('description')  optional meta description
      @section('page')         home, shop, product, cart or checkout: loads that page's CSS/JS bundle
      @section('body_wrap', 1) optional: wrap the page in .body_wrap (clips sideways overflow)
      @push('head')            optional extra tags (preloads, meta)
    CSS/JS come from public/assets/build (php artisan assets:build, see config/assets.php).
--}}
@php
    $page = trim($__env->yieldContent('page'));
    $bundles = config('assets.bundles');
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @hasSection('description')
    <meta name="description" content="@yield('description')">
    @endif
    <title>@yield('title') | Supplement Station</title>

    <link rel="icon" href="{{ asset('assets/img/logo/preloader2.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/logo/preloader2.png') }}">

    @stack('head')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@300;400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ \App\Services\Assets::url('store.css') }}">
    @if(isset($bundles["$page.css"]))
    <link rel="stylesheet" href="{{ \App\Services\Assets::url("$page.css") }}">
    @endif

    <script>
        window.SS = {
            baseUrl: @json(url('/')),
            csrf: @json(csrf_token()),
            routes: {
                cart: @json(route('api.cart.get')),
                cartRemove: @json(route('cart.remove')),
                cartUpdate: @json(route('cart.update')),
                cartPage: @json(route('cart')),
                checkoutPage: @json(route('checkout')),
                placeOrder: @json(route('place.order')),
                couponApply: @json(route('coupon.apply')),
                couponRemove: @json(route('coupon.remove')),
                couponApplied: @json(route('coupon.applied')),
                subscribe: @json(route('subscription.subscribe')),
                subscriptionCancel: @json(route('subscription.cancel')),
                subscriptionCheck: @json(route('subscription.check.modal')),
                recentPurchases: @json(route('api.recentPurchases'))
            }
        };
    </script>
    {{-- defer: downloads while the page is read, runs once it is parsed --}}
    <script src="{{ \App\Services\Assets::url('store.js') }}" defer></script>
    @if(isset($bundles["$page.js"]))
    <script src="{{ \App\Services\Assets::url("$page.js") }}" defer></script>
    @endif
</head>

<body>

<div class="xb-backtotop">
    <a href="#" class="scroll">
        <i class="far fa-arrow-up"></i>
    </a>
</div>

@hasSection('body_wrap')
<div class="body_wrap">
@endif

@include('partials.navBar')

<div class="body-overlay"></div>

@yield('content')

@include('partials.footer')

@hasSection('body_wrap')
</div>
@endif

</body>
</html>
