{{-- Store footer: logo and social links, shop and help links, contact details (#contact is kept for old links) --}}
@php use App\Support\Icons; @endphp
<footer class="ss-footer" id="contact">
    <div class="ss-wrap">
        <div class="ss-footer__cols">
            <div class="ss-footer__brand">
                <a href="{{ route('shop') }}" aria-label="Supplement Station home">
                    <img src="{{ asset('assets/img/logo/outlined-logo-nav.webp') }}" alt="Supplement Station" width="168" height="46" loading="lazy">
                </a>
                <p>Authentic supplements from the brands athletes trust, delivered all over Lebanon. Since 2018.</p>
                <div class="ss-footer__social">
                    <a href="https://wa.me/{{ config('store.whatsapp') }}" target="_blank" rel="noopener" aria-label="WhatsApp">{!! Icons::fa('whatsapp') !!}</a>
                    <a href="https://www.instagram.com/supplement_station_lb" target="_blank" rel="noopener" aria-label="Instagram">{!! Icons::fa('instagram') !!}</a>
                </div>
            </div>
            <nav aria-label="Categories">
                <h4>Shop</h4>
                @foreach(($footerCategories ?? collect())->take(6) as $category)
                    <a href="{{ $category->url() }}">{{ $category->name }}</a>
                @endforeach
                <a href="{{ route('shop') }}">All products</a>
            </nav>
            <nav aria-label="Help">
                <h4>Help</h4>
                <a href="{{ route('shop', ['sale' => 1]) }}">Deals</a>
                <a href="{{ route('cart') }}">Cart</a>
                <a href="{{ route('checkout') }}">Checkout</a>
            </nav>
            <div class="ss-footer__reach">
                <h4>Contact us</h4>
                <a href="tel:{{ config('store.phone_link') }}">{!! Icons::ui('phone') !!} {{ config('store.phone') }}</a>
                <a href="mailto:admin@supplement-station.com">{!! Icons::ui('mail') !!} admin@supplement-station.com</a>
                <span>{!! Icons::ui('pin') !!} {{ config('store.address') }}</span>
                <span>{!! Icons::ui('info') !!} {{ config('store.opening_hours') }}</span>
                <a href="https://wa.me/{{ config('store.whatsapp') }}?text={{ rawurlencode('Hello Supplement Station, ') }}" target="_blank" rel="noopener" class="ss-btn ss-btn--red ss-footer__wa">
                    {!! Icons::fa('whatsapp') !!} WhatsApp
                </a>
            </div>
        </div>

        <div class="ss-footer__bottom">
            <span>&copy; {{ date('Y') }} Supplement Station, owned by Amine el Sayed. All rights reserved.</span>
            <span>Powered by <a href="https://www.tawwer.tech/" target="_blank" rel="noopener">Tawwer</a></span>
        </div>
    </div>
</footer>

@include('partials/recentPurchaseToast')
