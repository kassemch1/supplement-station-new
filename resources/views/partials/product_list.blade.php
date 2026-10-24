
@if($product->isEmpty())
    <div class="no-products-found">
        <p>No products were found.</p>
    </div>
@else
    {{-- Both layouts are sent; the screen width picks one (a phone sees the 2-column one) --}}
        <!-- Mobile Layout -->
        <div class="row d-md-none">
            @foreach($product as $item)
                @php
                    $ratingCount = $item->reviews_count;
                    $averageRating = $ratingCount > 0 ? $item->reviews_avg_rating : 5;
                    $truncatedName = \Illuminate\Support\Str::limit($item->name, 54,'...');

                @endphp
                <div class="col-6">
                    @if((int)$item->stock === 1)
                        <a href="{{ route('products.show', $item->id) }}" class="product-link" style="text-decoration: none; color: inherit;">
                        @endif
                    <div class="product-cart">
                        <!-- Sale Label -->
                        <div style="width: 150px; height: 150px; overflow: hidden; position: absolute; top:0; right: 0px; z-index: 2;">
                            @if($item->discount > 0)
                                <span class="sale-labels">On Sale</span>
                            @endif
                        </div>

                        <!-- Product Image -->
                        <a href="{{ route('products.show', $item->id) }}" class="product-image">
                            @if($item->images->isNotEmpty())
                                <img src="{{ asset($item->images->first()->url) }}" alt="Product Image" width="300" height="300" style="width: 100%; height: auto; max-width: 100%; object-fit: contain; border-radius: 8px;" loading="lazy">
                            @else
                                <span style="display: block; width: 100%; height: 100%; background-color: #f0f0f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #888;">No image available</span>
                            @endif
                        </a>

                        <!-- Product Name -->
                        @if($item->stock > 0)
                            <a href="{{ route('products.show', $item->id) }}" class="product-name">
                                {{ $truncatedName }}
                            </a>
                        @else
                            <a href="{{ route('products.show', $item->id) }}" class="product-name">
                                {{ $truncatedName }}<br/>
                                <span class="out-of-stock">(out of stock)</span>
                            </a>
                        @endif

                        <!-- Rating -->
                        <div class="xb-item--rating-inner ul_li_center" style="margin-top: 5px;margin-bottom: 5px">
                            <ul class="xb-item--rating ul_li">
                                @for ($i = 0; $i < 5; $i++)
                                    <i class="fas fa-star{{ $i < $averageRating ? '' : '-o' }}"></i>
                                @endfor
                            </ul>
                        </div>

                        <!-- Price -->
                        <div>
                            @if($item->discount > 0)
                                <span style="text-decoration: line-through; color: gray; font-size:12px">${{ number_format($item->price, 2) }}</span>
                                <span style="color: #A02334;">${{ number_format($item->price - ($item->price * $item->discount / 100), 2) }}</span>
                            @else
                                ${{ number_format($item->price, 2) }}
                            @endif
                        </div>

                        <!-- Add to Cart Button -->
                        @if($item->stock > 0)
                            <a class="add-to-cart-button" href="{{ route('products.show', $item->id) }}" style="display: flex; align-items: center;">
                                <span class="xb-item--cart-icon" style="display: flex; justify-content: center; align-items: center; margin-right: 5px;">
                                    <img src="{{ asset('assets/img/icon/bag.svg') }}" alt="Cart" style="height: 15px; width: 15px;">
                                </span>
                                Add to Cart
                            </a>
                        @else
                            <a class="add-to-cart-button-disabled" href="{{ route('products.show', $item->id) }}">
                                <span class="xb-item--cart">Out of Stock</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <!-- Desktop Layout -->
        <div class="row d-none d-md-flex">
            @foreach($product as $item)
                @php
                    $ratingCount = $item->reviews_count;
                    $averageRating = $ratingCount > 0 ? $item->reviews_avg_rating : 5;
                    $truncatedName = \Illuminate\Support\Str::limit($item->name, 54);
                @endphp
                <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                    <div class="product product-item text-center" style="position: relative; overflow: hidden; min-height:450px">
                        @if($item->discount > 0)
                            <div class="ribbon" style="width: 150px; height: 150px; overflow: hidden; position: absolute; top: -10px; right: 0px; z-index: 2;">
                                <span style="position: absolute; display: block; width: 225px; padding: 15px 0; background-color: #A02334; color: white; text-transform: uppercase; font-weight: bold; text-align: center; transform: rotate(45deg); top: 30px; right: -65px;">
                                    On Sale
                                </span>
                            </div>
                        @endif
                        @if($item->stock > 0)
                            <a href="{{ route('products.show', $item->id) }}">
                                <div class="xb-item--img">
                                    @if($item->images->isNotEmpty())
                                    <img src="{{ asset($item->images->first()->url) }}" alt="img" width="155" height="170" style="width: 155px; height: 170px; object-fit: contain;" loading="lazy">
                                @else
                                        No image available
                                    @endif
                                </div>
                            </a>
                        @else
                            <a href="{{ route('products.show', $item->id) }}">
                                <div class="xb-item--img no-stock">
                                    @if($item->images->isNotEmpty())
                                        <img src="{{ asset($item->images->first()->url) }}" alt="img" width="155" height="170" style="width: 155px; height: 170px; object-fit: cover;" loading="lazy">
                                    @else
                                        No image available
                                    @endif
                                </div>
                            </a>
                        @endif
                        <div class="xb-item--holder">
                            <h3 class="xb-item--title">
                                @if($item->stock > 0)
                                    <a href="{{ route('products.show', $item->id) }}">{{ $truncatedName }}</a>
                                @else
                                    <a href="{{ route('products.show', $item->id) }}"><span class="no-stock-title">{{ $truncatedName }}<span style="opacity: 0.8; color: #ff0000;"><br/>(out of stock)</span></span></a>
                                @endif
                            </h3>
                            <div class="xb-item--rating-inner ul_li_center" style="margin-top: 10px;margin-bottom: 5px">
                                <ul class="xb-item--rating ul_li">
                                    @for ($i = 0; $i < 5; $i++)
                                        <i class="fas fa-star{{ $i < $averageRating ? '' : '-o' }}"></i>
                                    @endfor
                                </ul>
                            </div>
                        </div>
                        <div class="xb-item--action ul_li mt-20" style="position: absolute; bottom: 30px; width: 80%;">
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
                                <a href="{{ route('products.show', $item->id) }}" class="xb-item--cart-btn disabled">
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
