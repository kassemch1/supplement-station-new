{{--
    Layout of the store pages (home, shop, product, cart, checkout). A page sets:
      @section('title')        text before " | Supplement Station"
      @section('full_title')   optional: the whole title instead (home page)
      @section('description')  meta description (a default is used without one)
      @section('canonical')    optional: the address search engines should index (default: this one, no query)
      @section('robots')       optional: e.g. "noindex, follow" for the cart
      @section('og_type'), @section('og_image')  optional: link previews (WhatsApp, Facebook...)
      @push('schema')          optional: JSON-LD structured data (<script type="application/ld+json">)
      @section('page')         home, shop, product, cart or checkout: loads that page's CSS/JS bundle
      @section('body_wrap', 1) optional: wrap the page in .body_wrap (clips sideways overflow)
      @push('head')            optional extra tags (preloads, meta)
    CSS/JS come from public/assets/build (php artisan assets:build, see config/assets.php).
--}}
@php
    $page = trim($__env->yieldContent('page'));
    $bundles = config('assets.bundles');
    // Section values come back HTML-escaped already, so they are printed with {!! !!}
    $seoTitle = trim($__env->yieldContent('full_title')) ?: trim($__env->yieldContent('title')) . ' | Supplement Station';
    $seoDescription = trim($__env->yieldContent('description'))
        ?: e('Supplement Station, Beirut: protein, creatine, mass gainers, pre-workouts, vitamins and gym gear from trusted brands. Order online in Lebanon.');
    $seoCanonical = trim($__env->yieldContent('canonical')) ?: e(url()->current());
    $seoRobots = trim($__env->yieldContent('robots')) ?: 'index, follow, max-image-preview:large';
    $seoImage = trim($__env->yieldContent('og_image')) ?: e(asset('assets/img/logo/preloader2.png'));
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{!! $seoTitle !!}</title>
    <meta name="description" content="{!! $seoDescription !!}">
    <meta name="robots" content="{!! $seoRobots !!}">
    <link rel="canonical" href="{!! $seoCanonical !!}">
    <meta name="theme-color" content="#A02334">

    <meta property="og:site_name" content="Supplement Station">
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="{!! trim($__env->yieldContent('og_type')) ?: 'website' !!}">
    <meta property="og:title" content="{!! $seoTitle !!}">
    <meta property="og:description" content="{!! $seoDescription !!}">
    <meta property="og:url" content="{!! $seoCanonical !!}">
    <meta property="og:image" content="{!! $seoImage !!}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ asset('assets/img/logo/preloader2.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('assets/img/logo/preloader2.png') }}">

    @stack('head')
    @stack('schema')

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
