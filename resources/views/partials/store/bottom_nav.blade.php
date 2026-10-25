{{-- Phones, on the shop, product, cart and checkout pages: app-style tabs at the bottom of the screen --}}
@php
    use App\Support\Icons;
    $here = trim($__env->yieldContent('page'));
    $bestSellers = request()->is('shop') && request()->query('orderby') === 'best-selling';
@endphp
<nav class="ss-tabbar" aria-label="Main">
    <a href="{{ route('home') }}" class="ss-tabbar__item {{ $here === 'home' ? 'is-active' : '' }}">{!! Icons::ui('home') !!}<span>Home</span></a>
    <a href="{{ route('shop') }}" class="ss-tabbar__item {{ in_array($here, ['shop', 'product'], true) && !$bestSellers ? 'is-active' : '' }}">{!! Icons::ui('store') !!}<span>Shop</span></a>
    <a href="{{ route('shop', ['orderby' => 'best-selling']) }}" class="ss-tabbar__item {{ $bestSellers ? 'is-active' : '' }}">{!! Icons::ui('trophy') !!}<span>Best sellers</span></a>
    <a href="{{ route('cart') }}" class="ss-tabbar__item {{ in_array($here, ['cart', 'checkout'], true) ? 'is-active' : '' }}">
        <span class="ss-tabbar__icon">{!! Icons::ui('bag') !!}<span class="ss-tabbar__badge" data-cart-count hidden>0</span></span><span>Cart</span>
    </a>
    <a href="https://wa.me/{{ config('store.whatsapp') }}" class="ss-tabbar__item" target="_blank" rel="noopener">{!! Icons::ui('chat') !!}<span>WhatsApp</span></a>
</nav>
