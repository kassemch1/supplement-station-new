{{-- The cart panel that slides in from the side on every store page (filled by store.js), and the
     quick view the shop's + buttons open --}}
@php use App\Support\Icons; @endphp
<div class="ss-drawer" id="cart-drawer" role="dialog" aria-modal="true" aria-labelledby="cart-drawer-title" aria-hidden="true">
    <div class="ss-drawer__backdrop" data-close-cart></div>
    <div class="ss-drawer__panel">
        <div class="ss-drawer__head">
            <h2 id="cart-drawer-title">Your cart <span class="ss-drawer__count" data-cart-count-label></span></h2>
            <button type="button" class="ss-iconbtn" data-close-cart aria-label="Close cart">{!! Icons::ui('x') !!}</button>
        </div>
        <div class="ss-drawer__body">
            <div class="ss-drawer__lines" data-cart-lines>
                <div class="ss-skel-line"></div><div class="ss-skel-line"></div>
            </div>
            <section class="ss-suggest" data-suggest hidden>
                <div class="ss-suggest__head">
                    <h3>Complete your stack</h3>
                    <span class="ss-suggest__nav">
                        <button type="button" class="ss-iconbtn ss-iconbtn--sm" data-suggest-scroll="-1" aria-label="Previous">{!! Icons::ui('prev') !!}</button>
                        <button type="button" class="ss-iconbtn ss-iconbtn--sm" data-suggest-scroll="1" aria-label="Next">{!! Icons::ui('next') !!}</button>
                    </span>
                </div>
                <div class="ss-suggest__track" data-suggest-track></div>
            </section>
        </div>
        <div class="ss-drawer__foot" data-cart-foot hidden>
            <div class="ss-sumrow"><span>Subtotal</span><span data-sum-subtotal>$0.00</span></div>
            <div class="ss-sumrow ss-sumrow--discount" data-sum-discount-row hidden><span data-sum-coupon>Coupon</span><span data-sum-discount>-$0.00</span></div>
            <div class="ss-sumrow ss-sumrow--total"><span>Total</span><span data-sum-total>$0.00</span></div>
            <p class="ss-drawer__note">{!! Icons::ui('truck') !!} Delivery all over Lebanon, cash on delivery</p>
            <div class="ss-drawer__actions">
                <a href="{{ route('cart') }}" class="ss-btn ss-btn--outline">View cart</a>
                <a href="{{ route('checkout') }}" class="ss-btn ss-btn--red" data-checkout-link>Checkout {!! Icons::ui('arrow') !!}</a>
            </div>
        </div>
    </div>
</div>

<div class="ss-qv" id="quick-view" role="dialog" aria-modal="true" aria-label="Product quick view" aria-hidden="true">
    <div class="ss-qv__backdrop" data-close-qv></div>
    <div class="ss-qv__panel">
        <button type="button" class="ss-iconbtn ss-qv__close" data-close-qv aria-label="Close">{!! Icons::ui('x') !!}</button>
        <div class="ss-qv__content" data-qv-content></div>
    </div>
</div>

<div class="ss-toast" id="ss-toast" role="status" aria-live="polite"></div>
