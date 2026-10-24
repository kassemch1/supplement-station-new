{{-- "Our Best Offers" box of the shop page; shown in the sidebar on wider screens and under the products on phones --}}
<div class="widget mt-40 {{ $class ?? '' }}">
    <h2 class="widget__title">Our Best Offers</h2>
    <div class="widget__inner">
        <ul class="widget-product">
            @foreach($offersProducts as $offer)
                <li class="widget-product__item">
                    <div class="thumb">
                        <a href="{{ route('products.show', ['id' => $offer->id]) }}">
                            @if($offer->images->isNotEmpty())
                                <img src="{{ asset($offer->images->first()->url) }}" alt="" loading="lazy">
                            @else
                                No image available
                            @endif
                        </a>
                    </div>
                    <div class="content">
                        <h3>
                            <a href="{{ route('products.show', ['id' => $offer->id]) }}">{{ $offer->name }}</a>
                        </h3>
                        <span class="price">
                            @if($offer->discount > 0)
                                <span
                                    style="text-decoration: line-through; color: gray;">${{ number_format($offer->price, 2) }}</span>
                                <span
                                    style="color: #A02334;">${{ number_format($offer->price - ($offer->price * $offer->discount / 100), 2) }}</span>
                            @else
                                ${{ number_format($offer->price, 2) }}
                            @endif
                        </span>

                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
