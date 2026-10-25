@extends('layouts.store')

@section('title', 'Cart')
@section('robots', 'noindex, follow')
@section('page', 'cart')
@section('shell', 'app')

@php use App\Support\Icons; @endphp

@section('content')
<main class="ss-page ss-page--cart">
    <div class="ss-wrap">
        <div class="ss-pagehead">
            <div>
                <h1>Your cart <span class="ss-pagehead__count" data-cart-count-label></span></h1>
                <a href="{{ route('shop') }}" class="ss-back">{!! Icons::ui('back') !!} Continue shopping</a>
            </div>
            @include('partials.store.steps', ['step' => 1])
        </div>

        <div class="ss-split">
            <section class="ss-panel ss-cartlines">
                <div data-cart-lines>
                    <div class="ss-skel-line"></div><div class="ss-skel-line"></div><div class="ss-skel-line"></div>
                </div>
            </section>

            <aside class="ss-summary" data-cart-foot hidden>
                <div class="ss-panel">
                    <h2 class="ss-panel__title">Order summary</h2>
                    @include('partials.store.coupon')
                    <div class="ss-sums">
                        <div class="ss-sumrow"><span>Subtotal</span><span data-sum-subtotal>$0.00</span></div>
                        <div class="ss-sumrow ss-sumrow--discount" data-sum-discount-row hidden><span data-sum-coupon>Coupon</span><span data-sum-discount>-$0.00</span></div>
                        <div class="ss-sumrow"><span>Delivery</span><span class="ss-muted">Confirmed by phone</span></div>
                        <div class="ss-sumrow ss-sumrow--total"><span>Total</span><span data-sum-total>$0.00</span></div>
                    </div>
                    <a href="{{ route('checkout') }}" class="ss-btn ss-btn--red ss-btn--block ss-btn--lg" data-checkout-link>Checkout {!! Icons::ui('arrow') !!}</a>
                    @include('partials.store.trust')
                </div>
            </aside>
        </div>

        <section class="ss-suggest ss-suggest--page" data-suggest hidden>
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

    {{-- phones: the total and the checkout button stay above the tabs --}}
    <div class="ss-stickybar" data-cart-foot hidden>
        <div><span class="ss-stickybar__label">Total</span><strong data-sum-total>$0.00</strong></div>
        <a href="{{ route('checkout') }}" class="ss-btn ss-btn--red" data-checkout-link>Checkout {!! Icons::ui('arrow') !!}</a>
    </div>
</main>
@endsection
