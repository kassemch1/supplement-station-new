{{-- Store footer: logo and social links, shop and help links, contact details (the "Contact" links point at #contact) --}}
@php use App\Support\Icons; @endphp
<footer class="ss-footer" id="contact">
    <div class="ss-wrap">
        <div class="ss-footer__cols">
            <div class="ss-footer__brand">
                <a href="{{ route('home') }}" aria-label="Supplement Station home">
                    <img src="{{ asset('assets/img/logo/outlined-logo-nav.png') }}" alt="Supplement Station" width="168" height="46" loading="lazy">
                </a>
                <p>Authentic supplements from the brands athletes trust, delivered all over Lebanon. Since 2018.</p>
                <div class="ss-footer__social">
                    <a href="https://api.whatsapp.com/send?phone=96181823038" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://www.instagram.com/supplement_station_lb" target="_blank" rel="noopener" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
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
                <a href="tel:+96181823038">{!! Icons::ui('phone') !!} +961 81 823 038</a>
                <a href="tel:+96181088266">{!! Icons::ui('phone') !!} +961 81 088 266</a>
                <a href="mailto:admin@supplement-station.com">{!! Icons::ui('mail') !!} admin@supplement-station.com</a>
                <span>{!! Icons::ui('pin') !!} Msharafieh, facing Garden State, Beirut</span>
                <span>{!! Icons::ui('info') !!} Mon – Sat, 9:00 AM – 9:00 PM</span>
                <a href="https://wa.me/96181823038?text={{ rawurlencode('Hello Supplement Station, ') }}" target="_blank" rel="noopener" class="ss-btn ss-btn--red ss-footer__wa">
                    <i class="fab fa-whatsapp"></i> WhatsApp
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
