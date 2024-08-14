
@if($agent->isMobile())
    <!-- Mobile Layout -->
    <div class="row">
        @foreach($product as $item)
            <div class="col-6">
                <div class="product product-item text-center mobile-layout">
                    @if($item->discount > 0)
                        <div class="ribbon">
                            <span>On Sale</span>
                        </div>
                    @endif
                    @if($item->stock > 0)
                        <a href="{{ route('products.show', $item->id) }}">
                            <div class="xb-item--img">
                                @if($item->images->isNotEmpty())
                                    <img src="{{ asset($item->images->first()->url) }}" alt="img">
                                @else
                                    No image available
                                @endif
                            </div>
                        </a>
                    @else
                        <div class="xb-item--img no-stock">
                            @if($item->images->isNotEmpty())
                                <img src="{{ asset($item->images->first()->url) }}" alt="img">
                            @else
                                No image available
                            @endif
                        </div>
                    @endif
                    <div class="xb-item--holder">
                        <h3 class="xb-item--title">
                            @if($item->stock > 0)
                                <a href="{{ route('products.show', $item->id) }}" style="font-size: 15px">{{ $item->name }}</a>
                            @else
                                <span class="no-stock-title" style="font-size: 15px">{{ $item->name }}<span
                                        style="opacity: 0.8; color: #ff0000;"><br/>(out of stock)</span></span>
                            @endif
                        </h3>
                        <div class="xb-item--rating-inner ul_li_center">
                            <ul class="xb-item--rating ul_li">
                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                            </ul>
                        </div>
                    </div>
                    <div class="xb-item--action ul_li mt-20">
                        <span class="xb-item--price">
                            @if($item->discount > 0)
                                <span style="text-decoration: line-through; color: gray;">${{ number_format($item->price, 2) }}</span>
                                <span style="color: #A02334;">${{ number_format($item->price - ($item->price * $item->discount / 100), 2) }}</span>
                            @else
                                ${{ number_format($item->price, 2) }}
                            @endif
                        </span>

                    </div>
                    <div class="xb-item--action u_li mt-20">
                        @if($item->stock > 0)
                            <a href="{{ route('products.show', $item->id) }}" class="xb-item--cart-btn">
                                <span class="xb-item--cart-icon"><img src="{{ asset('assets/img/icon/bag.svg') }}" alt="Cart"></span>
                                <span class="xb-item--cart">Add to Cart</span>
                            </a>
                        @else
                            <a href="#" class="xb-item--cart-btn disabled" onclick="showOutOfStockMessage(event)">
                                <span class="xb-item--cart-icon"><img src="{{ asset('assets/img/icon/bag.svg') }}" alt="Cart"></span>
                                <span class="xb-item--cart">Out of Stock</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@else
    <!-- Desktop Layout -->
    <div class="row">
        @foreach($product as $item)
            <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                <div class="product product-item text-center"
                     style="position: relative; overflow: hidden; min-height:450px">
                    @if($item->discount > 0)
                        <div class="ribbon">
                            <span>On Sale</span>
                        </div>
                    @endif
                    @if($item->stock > 0)
                        <a href="{{ route('products.show', $item->id) }}">
                            <div class="xb-item--img">
                                @if($item->images->isNotEmpty())
                                    <img src="{{ asset($item->images->first()->url) }}" alt="img"
                                         style="width: 155px; height: 170px; object-fit: cover;">
                                @else
                                    No image available
                                @endif
                            </div>
                        </a>
                    @else
                        <div class="xb-item--img no-stock">
                            @if($item->images->isNotEmpty())
                                <img src="{{ asset($item->images->first()->url) }}" alt="img"
                                     style="width: 155px; height: 170px; object-fit: cover;">
                            @else
                                No image available
                            @endif
                        </div>
                    @endif
                    <div class="xb-item--holder">
                        <h3 class="xb-item--title">
                            @if($item->stock > 0)
                                <a href="{{ route('products.show', $item->id) }}">{{ $item->name }}</a>
                            @else
                                <span class="no-stock-title">{{ $item->name }}<span
                                        style="opacity: 0.8; color: #ff0000;"><br/>(out of stock)</span></span>
                            @endif
                        </h3>
                        <div class="xb-item--rating-inner ul_li_center">
                            <ul class="xb-item--rating ul_li">
                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                                <li><img src="{{ asset('assets/img/icon/star.png') }}" alt="Star"></li>
                            </ul>
                        </div>
                    </div>
                    <div class="xb-item--action ul_li mt-20"
                         style="position: absolute; bottom: 30px; width: 80%;">
                        <span class="xb-item--price">
                            @if($item->discount > 0)
                                <span style="text-decoration: line-through; color: gray;">${{ number_format($item->price, 2) }}</span>
                                <span style="color: #A02334;">${{ number_format($item->price - ($item->price * $item->discount / 100), 2) }}</span>
                            @else
                                ${{ number_format($item->price, 2) }}
                            @endif
                        </span>
                        @if($item->stock > 0)
                            <a href="{{ route('products.show', $item->id) }}" class="xb-item--cart-btn">
                                <span class="xb-item--cart-icon"><img src="{{ asset('assets/img/icon/bag.svg') }}" alt="Cart"></span>
                                <span class="xb-item--cart">Add to Cart</span>
                            </a>
                        @else
                            <a href="#" class="xb-item--cart-btn disabled" onclick="showOutOfStockMessage(event)">
                                <span class="xb-item--cart-icon"><img src="{{ asset('assets/img/icon/bag.svg') }}" alt="Cart"></span>
                                <span class="xb-item--cart">Out of Stock</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

<style>
    .mobile-layout .product-item {
        padding: 10px; /* Adjust padding for mobile */
        background-color: var(--color-white);
        box-sizing: border-box;
        min-height: auto; /* Adjust as needed */
    }

    .mobile-layout .xb-item--img img {
        width: 100%; /* Ensure images fit within the card */
        height: auto;
    }
</style>

