@extends('layouts.store')

@section('title', 'Buy Premium Supplements in Lebanon')
@section('page', 'home')
@section('shell', 'app')
@section('full_title', 'Supplement Station Lebanon | Protein, Creatine & Gym Supplements')
@section('description', 'Supplement Station in Beirut, Lebanon: whey protein, creatine, mass gainers, pre-workouts, vitamins and gym gear from Optimum Nutrition, MuscleTech, BPI and more. Order online.')

@section('canonical', url('/') . '/')
@push('schema')
    {{ \App\Support\Seo::script([\App\Support\Seo::store(), \App\Support\Seo::website(), \App\Support\Seo::faq($faqs)]) }}
@endpush

@push('head')
    <!-- hero background: the biggest thing on the screen, so fetch it before anything else -->
    <link rel="preload" as="image" href="{{ asset('assets/img/bg/home-banner.webp') }}" fetchpriority="high">
@endpush

@php
    use App\Support\Icons;
    $heroProduct = $banner?->product_id ? $banner->getProduct : null;
    $heroImage = $heroProduct && $banner->image ? asset($banner->image) : asset('assets/img/shop/hero_product.webp');
@endphp

@section('content')
<a href="https://wa.me/{{ config('store.whatsapp') }}" target="_blank" rel="noopener" class="whatsapp-popup" aria-label="WhatsApp">
    {!! Icons::fa('whatsapp') !!}
</a>

<main class="ss-home">
    {{-- ---------- banner: the original design ---------- --}}
    <section class="hero hero-style-one bg_img hero-height d-flex align-items-center ss-banner" style="background-image: url({{ asset('assets/img/bg/home-banner.webp') }})">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero__content sec-title">
                        <span class="sub-title">100% PREMIUM QUALITY</span>
                        <h1 class="title mb-25" style="text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.7); color: #fff;">
                            Today Elevate Your Energy Levels at Supplement Station
                        </h1>
                        <div class="hero__action ul_li">
                            <a class="thm-btn mr-45 mt-30" href="{{ $heroProduct ? $heroProduct->url() : route('shop') }}">BUY NOW</a>
                            <div class="hero__cta ul_li mt-30">
                                <span class="icon"><img src="{{ asset('assets/img/icon/call.svg') }}" alt=""></span>
                                <div class="info">
                                    <span style="color: #FFFFFF !important;">CONTACT US DAILY</span>
                                    <h4>{{ config('store.phone') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="hero__product text-md-end">
                        <img src="{{ $heroImage }}" alt="{{ $heroProduct?->name ?? 'Featured supplement' }}" width="500" height="500" fetchpriority="high">
                    </div>
                </div>
            </div>
        </div>
    </section>
    {{-- ---------- categories ---------- --}}
    @if($categoryTree->isNotEmpty())
        <section class="ss-wrap ss-hsec">
            <div class="ss-sechead">
                <h2>Shop by category</h2>
                <a href="{{ route('shop') }}" class="ss-sechead__link">All products {!! Icons::ui('arrow') !!}</a>
            </div>
            <div class="ss-hcats">
                @foreach($categoryTree as $main)
                    <a href="{{ $main->url() }}" class="ss-hcat">
                        <span class="ss-hcat__icon">{!! $main->iconSvg() !!}</span>
                        <span class="ss-hcat__name">{{ $main->name }}</span>
                        @if($main->children->isNotEmpty())
                            <span class="ss-hcat__subs">{{ $main->children->take(3)->pluck('name')->join(', ') }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ---------- special offers ---------- --}}
    <section class="ss-wrap ss-hsec">
        <div class="ss-rail" data-rail>
            <div class="ss-rail__head">
                <div>
                    <span class="ss-sechead__kicker">Limited time</span>
                    <h2>Special offers</h2>
                </div>
                <div class="ss-rail__nav">
                    <a href="{{ route('shop', ['sale' => 1]) }}" class="ss-sechead__link">See all {!! Icons::ui('arrow') !!}</a>
                    <button type="button" class="ss-iconbtn ss-iconbtn--sm" data-rail-scroll="-1" aria-label="Previous">{!! Icons::ui('prev') !!}</button>
                    <button type="button" class="ss-iconbtn ss-iconbtn--sm" data-rail-scroll="1" aria-label="Next">{!! Icons::ui('next') !!}</button>
                </div>
            </div>
            <div class="ss-rail__track" data-rail-track>
                @forelse($offersProducts as $item)
                    @include('partials.shop.card', ['item' => $item])
                @empty
                    <p class="ss-muted">No special offers right now.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ---------- brands: every brand in the shop, each opens its products. Computers: one row that slides
         with the arrows; phones: two rows to swipe ---------- --}}
    @if($brands->isNotEmpty())
        <section class="ss-wrap ss-hsec" data-rail>
            <div class="ss-sechead">
                <h2>Shop by brand</h2>
                <div class="ss-rail__nav">
                    <a href="{{ route('shop') }}" class="ss-sechead__link">All products {!! Icons::ui('arrow') !!}</a>
                    <button type="button" class="ss-iconbtn ss-iconbtn--sm" data-rail-scroll="-1" aria-label="Previous brands">{!! Icons::ui('prev') !!}</button>
                    <button type="button" class="ss-iconbtn ss-iconbtn--sm" data-rail-scroll="1" aria-label="Next brands">{!! Icons::ui('next') !!}</button>
                </div>
            </div>
            <div class="ss-hbrands" data-rail-track>
                @foreach($brands as $brand)
                    <a href="{{ route('shop', ['brand' => $brand->slug]) }}" class="ss-hbrand" title="{{ $brand->name }} products">
                        @if($brand->logoUrl())
                            <img src="{{ $brand->logoUrl() }}" alt="{{ $brand->name }}" loading="lazy" decoding="async">
                        @else
                            <span class="ss-hbrand__initials">{{ $brand->initials() }}</span>
                        @endif
                        <span class="ss-hbrand__name">{{ $brand->name }}</span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ---------- best sellers ---------- --}}
    <section class="ss-wrap ss-hsec">
        <div class="ss-rail" data-rail>
            <div class="ss-rail__head">
                <div>
                    <span class="ss-sechead__kicker">Customer favourites</span>
                    <h2>Best sellers</h2>
                </div>
                <div class="ss-rail__nav">
                    <a href="{{ route('shop') }}" class="ss-sechead__link">Shop all {!! Icons::ui('arrow') !!}</a>
                    <button type="button" class="ss-iconbtn ss-iconbtn--sm" data-rail-scroll="-1" aria-label="Previous">{!! Icons::ui('prev') !!}</button>
                    <button type="button" class="ss-iconbtn ss-iconbtn--sm" data-rail-scroll="1" aria-label="Next">{!! Icons::ui('next') !!}</button>
                </div>
            </div>
            <div class="ss-rail__track" data-rail-track>
                @forelse($product as $item)
                    @include('partials.shop.card', ['item' => $item])
                @empty
                    <p class="ss-muted">No products available.</p>
                @endforelse
            </div>
        </div>
    </section>

    {{-- ---------- about ---------- --}}
    <section class="ss-wrap ss-hsec">
        <div class="ss-about">
            <div class="ss-about__intro">
                <span class="ss-sechead__kicker">About Supplement Station</span>
                <h2>Your health journey starts here</h2>
                <p>Since 2018 we have helped athletes across Lebanon train harder and recover faster, with the highest quality supplements and honest advice.</p>
                <div class="ss-about__since"><strong>2018</strong><span>serving Lebanon since</span></div>
            </div>
            <ul class="ss-about__points">
                <li><span>{!! Icons::ui('shield') !!}</span><div><strong>Authentic products</strong>Sourced from the brands and their official distributors.</div></li>
                <li><span>{!! Icons::ui('truck') !!}</span><div><strong>Delivery all over Lebanon</strong>We call to confirm, then deliver to your door.</div></li>
                <li><span>{!! Icons::ui('lock') !!}</span><div><strong>Cash on delivery</strong>Pay when your order arrives. No card needed.</div></li>
                <li><span>{!! Icons::ui('chat') !!}</span><div><strong>Expert guidance</strong>Ask us anything on WhatsApp, every day.</div></li>
            </ul>
        </div>
    </section>

    {{-- ---------- questions ---------- --}}
    @if($faqs->isNotEmpty())
        <section class="ss-wrap ss-hsec ss-faq">
            <div class="ss-faq__intro">
                <span class="ss-sechead__kicker">Questions</span>
                <h2>Frequently asked questions</h2>
                <p>Everything about ordering, delivery and choosing the right supplements.</p>
                <div class="ss-faq__help">
                    <h3><span class="ss-faq__helpicon">{!! Icons::ui('chat') !!}</span> Still have a question?</h3>
                    <p>Our team answers on WhatsApp, Monday to Saturday, 9:00 AM – 9:00 PM.</p>
                    <div class="ss-faq__helpbtns">
                        <a href="https://wa.me/{{ config('store.whatsapp') }}?text={{ rawurlencode('Hello, I have a question: ') }}" target="_blank" rel="noopener" class="ss-btn ss-btn--red">
                            {!! Icons::fa('whatsapp') !!} WhatsApp
                        </a>
                        <a href="tel:{{ config('store.phone_link') }}" class="ss-btn ss-btn--outline">{!! Icons::ui('phone') !!} Call</a>
                    </div>
                </div>
            </div>
            <div class="ss-faq__list">
                @foreach($faqs as $faq)
                    <details class="ss-faq__item">
                        <summary>
                            <span class="ss-faq__num">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="ss-faq__q">{{ $faq->question }}</span>
                            <span class="ss-faq__toggle">{!! Icons::ui('plus') !!}</span>
                        </summary>
                        <p>{{ $faq->answer }}</p>
                    </details>
                @endforeach
            </div>
        </section>
    @endif
</main>
@endsection
