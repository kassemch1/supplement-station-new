{{-- Computers: the store header (phones keep the theme header below, or the app header on shop pages) --}}
@php
    use App\Support\Icons;
    $navHere = request()->routeIs('home') ? 'home'
        : (request()->routeIs('shop', 'shop.category', 'products.show') ? 'shop' : '');
@endphp
<div class="ss-topnav" id="ss-topnav">
    <div class="ss-topnav__inner">
        <a href="{{ route('shop') }}" class="ss-topnav__logo" aria-label="Supplement Station home">
            <img src="{{ asset('assets/img/logo/outlined-logo-nav.webp') }}" alt="Supplement Station" width="176" height="48">
        </a>

        <nav class="ss-topnav__links" aria-label="Main menu">
            <a href="{{ route('home') }}" class="ss-topnav__link {{ $navHere === 'home' ? 'is-active' : '' }}">Home</a>
            <a href="{{ route('shop') }}" class="ss-topnav__link {{ $navHere === 'shop' ? 'is-active' : '' }}">Shop</a>

            <div class="ss-topnav__menu">
                <button type="button" class="ss-topnav__link" aria-haspopup="true">Categories {!! Icons::ui('down', 'ss-ui ss-topnav__chev') !!}</button>
                <div class="ss-mega" role="menu">
                    <div class="ss-mega__grid">
                        @foreach($categoryTree as $main)
                            <div class="ss-mega__col">
                                <a href="{{ $main->url() }}" class="ss-mega__main">
                                    <span class="ss-mega__icon">{!! $main->iconSvg() !!}</span>{{ $main->name }}
                                </a>
                                @foreach($main->children as $sub)
                                    <a href="{{ $sub->url() }}" class="ss-mega__sub">{{ $sub->name }}</a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <a href="{{ route('shop') }}" class="ss-mega__all">Browse all products {!! Icons::ui('arrow') !!}</a>
                </div>
            </div>

            @if($brands->isNotEmpty())
                <div class="ss-topnav__menu">
                    <button type="button" class="ss-topnav__link" aria-haspopup="true">Brands {!! Icons::ui('down', 'ss-ui ss-topnav__chev') !!}</button>
                    <div class="ss-mega ss-mega--brands" role="menu">
                        <div class="ss-mega__brands">
                            @foreach($brands as $brand)
                                <a href="{{ route('shop', ['brand' => $brand->slug]) }}" class="ss-mega__brand" title="{{ $brand->name }}">
                                    @if($brand->logoUrl())
                                        <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->name }}" loading="lazy" decoding="async">
                                    @else
                                        <span class="ss-mega__initials">{{ $brand->initials() }}</span>
                                    @endif
                                    <span class="ss-mega__brandname">{{ $brand->name }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

        </nav>

        <form class="ss-topnav__search" action="{{ route('shop') }}" method="get" role="search" data-app-search>
            {!! Icons::ui('search') !!}
            <input type="search" name="q" placeholder="Search products or brands" aria-label="Search products" value="{{ \App\Services\Catalog::text(request(), 'q') }}" autocomplete="off">
        </form>

        <button type="button" class="ss-topnav__cart" data-open-cart aria-label="Open cart">
            {!! Icons::ui('bag') !!}<span>Cart</span><span class="ss-topnav__count" data-cart-count>0</span>
        </button>
    </div>
</div>
