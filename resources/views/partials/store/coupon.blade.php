{{-- Coupon box of the cart and checkout pages (store.js: SS.coupon) --}}
@php use App\Support\Icons; @endphp
<div class="ss-coupon" data-coupon>
    <div class="ss-coupon__form" data-coupon-form>
        <span class="ss-coupon__icon">{!! Icons::ui('ticket') !!}</span>
        <input type="text" placeholder="Coupon code" aria-label="Coupon code" data-coupon-input autocomplete="off" autocapitalize="characters">
        <button type="button" class="ss-btn ss-btn--dark ss-btn--sm" data-coupon-apply>Apply</button>
    </div>
    <div class="ss-coupon__applied" data-coupon-applied hidden>
        <span class="ss-coupon__icon">{!! Icons::ui('check') !!}</span>
        <span class="ss-coupon__text"><strong data-coupon-code></strong> <span data-coupon-name></span></span>
        <button type="button" class="ss-coupon__remove" data-coupon-remove>Remove</button>
    </div>
    <p class="ss-coupon__msg" data-coupon-msg></p>
</div>
