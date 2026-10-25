{{-- One product in the shop grid. The picture and name open the product page; + opens the quick view. --}}
@php
    $image = $item->images->first();
    $final = $item->price_after_discount;
    $inStock = (bool) $item->stock;
    $rating = $item->reviews_count > 0 ? round((float) $item->reviews_avg_rating, 1) : null;
@endphp
<article class="ss-card {{ $inStock ? '' : 'is-soldout' }}">
    <div class="ss-card__top">
    <a href="{{ $item->url() }}" class="ss-card__media" tabindex="-1" aria-hidden="true">
        @if($image)
            <img src="{{ asset($image->url) }}" alt="" width="300" height="300"
                 @if($eager ?? false) fetchpriority="high" @else loading="lazy" decoding="async" @endif>
        @else
            <span class="ss-card__noimg">No image</span>
        @endif
        <span class="ss-card__badges">
            @if(!$inStock)
                <span class="ss-tag ss-tag--dark">Sold out</span>
            @elseif($item->discount > 0)
                <span class="ss-tag ss-tag--red">-{{ rtrim(rtrim(number_format($item->discount, 1), '0'), '.') }}%</span>
            @endif
        </span>
    </a>
    @if($inStock)
        <button type="button" class="ss-card__add" data-quick="{{ $item->id }}" aria-label="Quick add {{ $item->name }}">
            {!! \App\Support\Icons::ui('plus') !!}
        </button>
    @endif
    </div>
    <div class="ss-card__body">
        @if($item->brand)
            <span class="ss-card__brand">{{ $item->brand->name }}</span>
        @endif
        <h3 class="ss-card__name"><a href="{{ $item->url() }}">{{ $item->name }}</a></h3>
        <div class="ss-card__foot">
            <span class="ss-price">
                <span class="ss-price__now">${{ number_format($final, 2) }}</span>
                @if($item->discount > 0)
                    <s class="ss-price__was">${{ number_format($item->price, 2) }}</s>
                @endif
            </span>
            @if($rating)
                <span class="ss-rating" title="{{ $rating }} out of 5">{!! \App\Support\Icons::ui('star') !!} {{ $rating }}</span>
            @endif
        </div>
    </div>
</article>
