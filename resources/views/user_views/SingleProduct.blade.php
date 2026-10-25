@extends('layouts.store')

@php
    use App\Support\Icons;
    $seoPrice = '$' . number_format($product->price_after_discount, 2);
    $seoImage = $product->images->first() ? asset($product->images->first()->url) : null;
    $inStock = (bool) $product->stock;
    $final = $product->price_after_discount;
    $main = $category?->main();
    $rounded = $ratingCount ? round((float) $averageRating, 1) : 0;

    // Flavour, size, colour...: the values of each option, in the order the admin added them
    $groupedOptions = $product->productOptions->groupBy(fn ($value) => $value->option->option_name);
@endphp
@section('title', $product->name . ' Price in Lebanon')
@section('description', \App\Support\Seo::text(
    'Buy ' . $product->name . ' in Lebanon at Supplement Station'
    . ($product->stock > 0 ? ' for ' . $seoPrice . '.' : '.') . ' ' . $product->description
))
@section('canonical', $product->url())
@section('og_type', 'product')
@if($seoImage)
    @section('og_image', $seoImage)
@endif
@push('head')
    <meta property="product:price:amount" content="{{ number_format($product->price_after_discount, 2, '.', '') }}">
    <meta property="product:price:currency" content="USD">
    <meta property="product:availability" content="{{ $product->stock > 0 ? 'in stock' : 'out of stock' }}">
@endpush
@push('schema')
    {{ \App\Support\Seo::script([
        \App\Support\Seo::product($product, $category?->name, $reviews),
        \App\Support\Seo::breadcrumbs(array_filter([
            ['Home', route('home')],
            ['Shop', route('shop')],
            $category ? [$category->name, $category->url()] : null,
            [$product->name, $product->url()],
        ])),
    ]) }}
@endpush
@section('page', 'product')
@section('shell', 'app')

@section('content')
@include('partials.subscription-modal', ['delay' => 2000])

<main class="ss-pdp">
    <div class="ss-wrap">
        <nav class="ss-crumbs" aria-label="Breadcrumb">
            <a href="{{ route('home') }}">Home</a><span>/</span>
            <a href="{{ route('shop') }}">Shop</a><span>/</span>
            @if($main && $main->id !== $category->id)
                <a href="{{ $main->url() }}">{{ $main->name }}</a><span>/</span>
            @endif
            @if($category)
                <a href="{{ $category->url() }}">{{ $category->name }}</a><span>/</span>
            @endif
            <span aria-current="page">{{ $product->name }}</span>
        </nav>

        <div class="ss-pdp__grid">
            {{-- Pictures: swipe on phones, thumbnails underneath --}}
            <section class="ss-pdp__gallery" aria-label="Pictures">
                <div class="ss-pdp__main" data-pdp-main>
                    @forelse($product->images as $index => $image)
                        <img src="{{ asset($image->url) }}" alt="{{ $product->name }}{{ $index ? ' picture ' . ($index + 1) : '' }}" width="500" height="500"
                             @if($index === 0) fetchpriority="high" @else loading="lazy" decoding="async" @endif data-pdp-slide="{{ $index }}">
                    @empty
                        <span class="ss-card__noimg">No picture</span>
                    @endforelse
                </div>
                <span class="ss-pdp__badges">
                    @if(!$inStock)
                        <span class="ss-tag ss-tag--dark">Sold out</span>
                    @elseif($product->discount > 0)
                        <span class="ss-tag ss-tag--red">-{{ rtrim(rtrim(number_format($product->discount, 1), '0'), '.') }}%</span>
                    @endif
                </span>
                @if($product->images->count() > 1)
                    <div class="ss-pdp__thumbs" role="tablist" aria-label="Choose a picture">
                        @foreach($product->images as $index => $image)
                            <button type="button" class="{{ $index === 0 ? 'is-active' : '' }}" data-pdp-thumb="{{ $index }}" aria-label="Picture {{ $index + 1 }}">
                                <img src="{{ asset($image->url) }}" alt="" width="64" height="64" loading="lazy" decoding="async">
                            </button>
                        @endforeach
                    </div>
                    <div class="ss-pdp__dots" aria-hidden="true">
                        @foreach($product->images as $index => $image)
                            <span class="{{ $index === 0 ? 'is-active' : '' }}"></span>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Name, price, options and the buy button. On phones this is a sheet that overlaps the picture. --}}
            <section class="ss-pdp__info">
                <div class="ss-pdp__top">
                    @if($product->brand)
                        <a href="{{ route('shop', ['brand' => $product->brand->slug]) }}" class="ss-pbrand">
                            @if($product->brand->logoUrl())<img src="{{ $product->brand->logoUrl() }}" alt="" width="56" height="28">@endif
                            <span>{{ $product->brand->name }}</span>
                        </a>
                    @endif
                    <h1>{{ $product->name }}</h1>
                    <a href="#reviews" class="ss-pdp__rating">
                        <span class="ss-stars" style="--rating: {{ $rounded }}" aria-hidden="true"></span>
                        @if($ratingCount)
                            <strong>{{ $rounded }}</strong> · {{ $ratingCount }} {{ \Illuminate\Support\Str::plural('review', $ratingCount) }}
                        @else
                            Be the first to review
                        @endif
                    </a>
                </div>

                <div class="ss-pdp__price">
                    <span class="ss-pdp__now">${{ number_format($final, 2) }}</span>
                    @if($product->discount > 0)
                        <s>${{ number_format($product->price, 2) }}</s>
                        <span class="ss-tag ss-tag--soft">You save ${{ number_format($product->price - $final, 2) }}</span>
                    @endif
                </div>
                <p class="ss-pdp__stock {{ $inStock ? 'is-in' : 'is-out' }}">
                    <span></span>{{ $inStock ? 'In stock, ready to deliver' : 'Sold out for now' }}
                </p>

                <form class="ss-pdp__form" action="{{ route('cart.add') }}" method="POST" id="add-to-cart-form">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <input type="hidden" name="selected_options" id="selected_options" value="{}">

                    @foreach($groupedOptions as $optionName => $values)
                        <div class="ss-opt" data-option-group="{{ $optionName }}">
                            <span class="ss-opt__label">{{ $optionName }}: <em data-option-chosen="{{ $optionName }}">Choose</em></span>
                            <div class="ss-opt__values">
                                @foreach($values as $value)
                                    @php $optionImage = $value->imageUrl(); @endphp
                                    @if($value->stock > 0)
                                        <button type="button" class="option-value ss-optbtn {{ $optionImage ? 'has-img' : '' }}"
                                                data-option-name="{{ $optionName }}" data-option-value="{{ $value->option_value }}"
                                                @if($optionImage) data-image="{{ $optionImage }}" @endif>
                                            @if($optionImage)<img src="{{ $optionImage }}" alt="" width="32" height="32" loading="lazy">@endif
                                            {{ $value->option_value }}
                                        </button>
                                    @else
                                        <span class="ss-optbtn is-out {{ $optionImage ? 'has-img' : '' }}" title="Out of stock">
                                            @if($optionImage)<img src="{{ $optionImage }}" alt="" width="32" height="32" loading="lazy">@endif
                                            {{ $value->option_value }}
                                        </span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    <div class="ss-pdp__buy">
                        <div class="ss-qty ss-qty--lg">
                            <button type="button" data-pdp-step="-1" aria-label="Less">{!! Icons::ui('minus') !!}</button>
                            <input type="number" name="quantity" id="quantity" value="1" min="1" max="100" aria-label="Quantity">
                            <button type="button" data-pdp-step="1" aria-label="More">{!! Icons::ui('plus') !!}</button>
                        </div>
                        @if($inStock)
                            <button type="submit" class="ss-btn ss-btn--red ss-btn--lg add-to-cart" data-product-id="{{ $product->id }}">
                                {!! Icons::ui('bag') !!} Add to cart
                            </button>
                        @else
                            <button type="button" class="ss-btn ss-btn--lg ss-btn--dark" disabled>Sold out</button>
                        @endif
                    </div>
                    <p class="ss-pdp__msg" data-pdp-msg role="status" aria-live="polite"></p>
                </form>

                @include('partials.store.trust')

                <dl class="ss-pdp__meta">
                    @if($category)
                        <div><dt>Category</dt><dd><a href="{{ $category->url() }}">{{ $category->name }}</a></dd></div>
                    @endif
                    @if($product->brand)
                        <div><dt>Brand</dt><dd><a href="{{ route('shop', ['brand' => $product->brand->slug]) }}">{{ $product->brand->name }}</a></dd></div>
                    @endif
                    <div><dt>Questions?</dt><dd><a href="https://wa.me/96181823038?text={{ rawurlencode('Hello, about ' . $product->name . ': ') }}" target="_blank" rel="noopener">WhatsApp</a></dd></div>
                </dl>
            </section>
        </div>

        <div class="ss-pdp__more">
            <section class="ss-panel ss-pdp__desc">
                <h2 class="ss-panel__title">About this product</h2>
                <div class="ss-pdp__text" data-pdp-text>{!! nl2br(e($product->description ?: $product->name)) !!}</div>
                <button type="button" class="ss-pdp__readmore" data-pdp-readmore hidden>Read more {!! Icons::ui('down') !!}</button>
            </section>

            <section class="ss-panel ss-reviews" id="reviews">
                <div class="ss-reviews__head">
                    <h2 class="ss-panel__title">Reviews</h2>
                    <div class="ss-reviews__score">
                        <strong>{{ $ratingCount ? $rounded : '–' }}</strong>
                        <span class="ss-stars" style="--rating: {{ $ratingCount ? $rounded : 0 }}" aria-hidden="true"></span>
                        <span>{{ $ratingCount }} {{ \Illuminate\Support\Str::plural('review', $ratingCount) }}</span>
                    </div>
                </div>

                <div class="ss-reviews__list" id="reviewsSection">
                    @forelse($reviews->sortByDesc('id') as $review)
                        <article class="ss-review">
                            <span class="ss-review__avatar">{{ mb_strtoupper(mb_substr(trim($review->name), 0, 1)) }}</span>
                            <div>
                                <div class="ss-review__top">
                                    <strong>{{ $review->name }}</strong>
                                    <span class="ss-stars ss-stars--sm" style="--rating: {{ (int) $review->rating }}" aria-label="{{ (int) $review->rating }} out of 5"></span>
                                </div>
                                <p>{{ $review->message }}</p>
                            </div>
                        </article>
                    @empty
                        <p class="ss-muted" data-no-reviews>No reviews yet. Tried it? Tell others what you think.</p>
                    @endforelse
                </div>

                <form id="reviewForm" class="ss-reviewform" data-url="{{ route('product.createReview', $product->id) }}">
                    @csrf
                    <h3>Write a review</h3>
                    <div class="ss-starpick" role="radiogroup" aria-label="Your rating">
                        @for ($i = 5; $i >= 1; $i--)
                            <input type="radio" name="rating" value="{{ $i }}" id="rate-{{ $i }}" required>
                            <label for="rate-{{ $i }}" title="{{ $i }} star{{ $i > 1 ? 's' : '' }}">{!! Icons::ui('star') !!}</label>
                        @endfor
                    </div>
                    <input type="text" class="ss-input" name="name" placeholder="Your name" maxlength="255" required>
                    <textarea class="ss-input" name="message" rows="3" placeholder="What did you think?" maxlength="5000" required></textarea>
                    <button class="ss-btn ss-btn--dark" type="submit">Post review</button>
                    <p class="ss-pdp__msg" id="successMessage" role="status" aria-live="polite"></p>
                </form>
            </section>
        </div>

        @if($relatedProducts->isNotEmpty())
            <section class="ss-rail" data-rail>
                <div class="ss-rail__head">
                    <h2>You may also like</h2>
                    <div class="ss-rail__nav">
                        <button type="button" class="ss-iconbtn ss-iconbtn--sm" data-rail-scroll="-1" aria-label="Previous">{!! Icons::ui('prev') !!}</button>
                        <button type="button" class="ss-iconbtn ss-iconbtn--sm" data-rail-scroll="1" aria-label="Next">{!! Icons::ui('next') !!}</button>
                    </div>
                </div>
                <div class="ss-rail__track" data-rail-track>
                    @foreach($relatedProducts as $item)
                        @include('partials.shop.card', ['item' => $item])
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    {{-- Phones: price and the buy button stay on screen above the tabs --}}
    <div class="ss-stickybar ss-pdp__bar">
        <div>
            <span class="ss-stickybar__label">{{ $inStock ? 'Price' : 'Sold out' }}</span>
            <strong>${{ number_format($final, 2) }}</strong>
        </div>
        @if($inStock)
            <button type="submit" form="add-to-cart-form" class="ss-btn ss-btn--red">{!! Icons::ui('bag') !!} Add to cart</button>
        @else
            <a href="{{ $category ? $category->url() : route('shop') }}" class="ss-btn ss-btn--dark">See similar</a>
        @endif
    </div>
</main>
@endsection
