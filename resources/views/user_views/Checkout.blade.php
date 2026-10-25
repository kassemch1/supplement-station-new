@extends('layouts.store')

@section('title', 'Checkout')
@section('robots', 'noindex, follow')
@section('page', 'checkout')
@section('shell', 'app')

@php
    use App\Support\Icons;
    // Suggestions for the city field; any other place can be typed
    $cities = ['Beirut', 'Achrafieh', 'Hamra', 'Mar Mikhael', 'Badaro', 'Verdun', 'Ras Beirut', 'Dekwaneh', 'Sin El Fil', 'Jdeideh', 'Bourj Hammoud',
        'Dora', 'Zalka', 'Jal El Dib', 'Antelias', 'Dbayeh', 'Naccache', 'Rabieh', 'Jounieh', 'Kaslik', 'Zouk Mosbeh', 'Byblos (Jbeil)', 'Batroun',
        'Tripoli', 'Baabda', 'Hazmieh', 'Hadath', 'Choueifat', 'Aramoun', 'Khalde', 'Dahieh', 'Aley', 'Broummana', 'Beit Mery', 'Bikfaya',
        'Mansourieh', 'Fanar', 'Zahle', 'Chtaura', 'Saida', 'Tyre (Sour)', 'Nabatieh', 'Baalbek'];
@endphp

@section('content')
<main class="ss-page ss-checkout">
    <div class="ss-wrap">
        <div class="ss-pagehead" data-checkout-head>
            <div>
                <h1>Checkout</h1>
                <a href="{{ route('cart') }}" class="ss-back">{!! Icons::ui('back') !!} Back to cart</a>
            </div>
            @include('partials.store.steps', ['step' => 2])
        </div>

        <form id="checkout_form" class="ss-split ss-split--checkout" method="post" action="{{ route('place.order') }}" novalidate data-checkout-form>
            @csrf
            {{-- Same token on every submit of this page, so a double-click or retry can't create a second order --}}
            <input type="hidden" name="checkout_token" value="{{ \Illuminate\Support\Str::uuid() }}">

            <div class="ss-checkout__main">
                <section class="ss-panel">
                    <h2 class="ss-panel__title"><span class="ss-panel__num">1</span> Your details</h2>
                    <div class="ss-fields">
                        <div class="ss-field ss-field--full">
                            <label for="billing_first_name">Full name</label>
                            <input type="text" name="billing_first_name" id="billing_first_name" autocomplete="name" required maxlength="255" placeholder="e.g. Karim Haddad">
                            <span class="ss-field__error" id="billing_first_name_error"></span>
                        </div>
                        <div class="ss-field">
                            <label for="billing_phone">Phone number</label>
                            <div class="ss-phone">
                                <span class="ss-phone__prefix">🇱🇧 +961</span>
                                <input type="tel" name="billing_phone" id="billing_phone" autocomplete="tel-national" inputmode="tel" required placeholder="03 123 456">
                            </div>
                            <span class="ss-field__hint">We call you on this number to confirm the delivery.</span>
                            <span class="ss-field__error" id="billing_phone_error"></span>
                        </div>
                        <div class="ss-field">
                            <label for="billing_email">Email <em>(optional)</em></label>
                            <input type="email" name="billing_email" id="billing_email" autocomplete="email" maxlength="255" placeholder="you@example.com">
                            <span class="ss-field__hint">Get your receipt by email.</span>
                            <span class="ss-field__error" id="billing_email_error"></span>
                        </div>
                    </div>
                </section>

                <section class="ss-panel">
                    <h2 class="ss-panel__title"><span class="ss-panel__num">2</span> Delivery address</h2>
                    <div class="ss-fields">
                        <div class="ss-field">
                            <label for="billing_city">City / area</label>
                            <input type="text" name="billing_city" id="billing_city" list="ss-cities" autocomplete="address-level2" required maxlength="255" placeholder="e.g. Achrafieh">
                            <datalist id="ss-cities">
                                @foreach($cities as $city)<option value="{{ $city }}">@endforeach
                            </datalist>
                            <span class="ss-field__error" id="billing_city_error"></span>
                        </div>
                        <div class="ss-field ss-field--full">
                            <label for="billing_address_1">Address</label>
                            <textarea name="billing_address_1" id="billing_address_1" rows="2" autocomplete="street-address" required maxlength="1000" placeholder="Street, building, floor, a landmark near you"></textarea>
                            <span class="ss-field__error" id="billing_address_1_error"></span>
                        </div>
                    </div>
                </section>

                <section class="ss-panel">
                    <h2 class="ss-panel__title"><span class="ss-panel__num">3</span> Payment</h2>
                    <label class="ss-paycard is-selected">
                        <input type="radio" checked disabled>
                        <span class="ss-paycard__radio"></span>
                        <span><strong>Cash on delivery</strong><small>Pay in cash when your order arrives.</small></span>
                        {!! Icons::ui('truck', 'ss-ui ss-paycard__icon') !!}
                    </label>
                </section>
            </div>

            <aside class="ss-summary">
                <div class="ss-panel">
                    <button type="button" class="ss-summary__toggle" data-summary-toggle aria-expanded="false">
                        <span>{!! Icons::ui('bag') !!} Order summary <span data-cart-count-label></span></span>
                        <span><strong data-sum-total>$0.00</strong> {!! Icons::ui('down', 'ss-ui ss-summary__chev') !!}</span>
                    </button>
                    <div class="ss-summary__body" data-summary-body>
                        <h2 class="ss-panel__title ss-summary__title">Order summary</h2>
                        <ul class="ss-olist" data-order-lines></ul>
                        @include('partials.store.coupon')
                        <div class="ss-sums">
                            <div class="ss-sumrow"><span>Subtotal</span><span data-sum-subtotal>$0.00</span></div>
                            <div class="ss-sumrow ss-sumrow--discount" data-sum-discount-row hidden><span data-sum-coupon>Coupon</span><span data-sum-discount>-$0.00</span></div>
                            <div class="ss-sumrow"><span>Delivery</span><span class="ss-muted">Confirmed by phone</span></div>
                            <div class="ss-sumrow ss-sumrow--total"><span>Total</span><span data-sum-total>$0.00</span></div>
                        </div>
                    </div>
                    <div class="ss-placeorder">
                        <p class="ss-formerror" data-form-error hidden></p>
                        <button type="submit" id="place_order_btn" class="ss-btn ss-btn--red ss-btn--block ss-btn--lg" disabled>
                            {!! Icons::ui('lock') !!} <span data-place-label>Place order</span>
                        </button>
                        <p class="ss-placeorder__note">You pay when the order arrives. We'll call you to confirm.</p>
                    </div>
                    @include('partials.store.trust')
                </div>
            </aside>
        </form>

        {{-- Shown in place of the form once the order is placed --}}
        <section class="ss-done" data-order-done hidden>
            <div class="ss-done__icon">{!! Icons::ui('done') !!}</div>
            <h1>Thank you, <span data-done-name></span>!</h1>
            <p class="ss-done__lead">Your order <strong data-done-number></strong> has been placed.</p>
            <p>We'll call you on <strong data-done-phone></strong> to confirm the delivery.<span data-done-email hidden> A receipt is on its way to <strong></strong>.</span></p>
            <div class="ss-panel ss-done__summary">
                <ul class="ss-olist" data-done-lines></ul>
                <div class="ss-sumrow ss-sumrow--total"><span>Total</span><span data-done-total></span></div>
            </div>
            <a href="{{ route('shop') }}" class="ss-btn ss-btn--dark ss-btn--lg">Continue shopping {!! Icons::ui('arrow') !!}</a>
        </section>
    </div>
</main>
@endsection
