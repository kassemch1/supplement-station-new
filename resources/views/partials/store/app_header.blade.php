{{-- Phones, on the shop, product, cart and checkout pages: a compact header instead of the full menu --}}
@php use App\Support\Icons; @endphp
<div class="ss-apphead" role="banner">
    <a href="{{ route('shop') }}" class="ss-apphead__logo" aria-label="Supplement Station home">
        <img src="{{ asset('assets/img/logo/preloader2.png') }}" alt="Supplement Station" width="40" height="40">
    </a>
    <form class="ss-apphead__search" action="{{ route('shop') }}" method="get" role="search" data-app-search>
        {!! Icons::ui('search') !!}
        <input type="search" name="q" placeholder="Search protein, brands…" aria-label="Search products" value="{{ \App\Services\Catalog::text(request(), 'q') }}" autocomplete="off">
    </form>
    <button type="button" class="ss-cartpill" data-open-cart aria-label="Open cart">
        {!! Icons::ui('bag') !!}<span class="ss-cartpill__count" data-cart-count>0</span>
    </button>
</div>
