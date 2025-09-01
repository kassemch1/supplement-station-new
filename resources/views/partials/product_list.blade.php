
@if($product->isEmpty())
    <div class="no-products-found">
        <p>No products were found.</p>
    </div>
@else
    @if($agent->isMobile())
        <!-- Mobile Layout -->
        <div class="row">
            @foreach($product as $item)
                @php
                    $ratingCount = $item->reviews()->count();
                    $averageRating = $ratingCount > 0 ? $item->reviews()->avg('rating') : 5;
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
                                <img src="{{ asset($item->images->first()->url) }}" alt="Product Image" style="width: 100%; height: auto; max-width: 100%; object-fit: contain; border-radius: 8px;" loading="lazy">
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
                            <span class="product-name">
                                {{ $truncatedName }}<br/>
                                <span class="out-of-stock">(out of stock)</span>
                            </span>
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
                            <a class="add-to-cart-button-disabled">
                                <span class="xb-item--cart">Out of Stock</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Desktop Layout -->
        <div class="row">
            @foreach($product as $item)
                @php
                    $ratingCount = $item->reviews()->count();
                    $averageRating = $ratingCount > 0 ? $item->reviews()->avg('rating') : 5;
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
                                    <img src="{{ asset($item->images->first()->url) }}" alt="img" style="width: 155px; height: 170px; object-fit: contain;" loading="lazy">
                                @else
                                        No image available
                                    @endif
                                </div>
                            </a>
                        @else
                            <div class="xb-item--img no-stock">
                                @if($item->images->isNotEmpty())
                                    <img src="{{ asset($item->images->first()->url) }}" alt="img" style="width: 155px; height: 170px; object-fit: cover;" loading="lazy">
                                @else
                                    No image available
                                @endif
                            </div>
                        @endif
                        <div class="xb-item--holder">
                            <h3 class="xb-item--title">
                                @if($item->stock > 0)
                                    <a href="{{ route('products.show', $item->id) }}">{{ $truncatedName }}</a>
                                @else
                                    <span class="no-stock-title">{{ $truncatedName }}<span style="opacity: 0.8; color: #ff0000;"><br/>(out of stock)</span></span>
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
@endif

<style>
    .no-products-found {
        text-align: center;
        padding: 20px;
        background-color: #f8f8f8;
        /*border: 1px solid #ddd;*/
        margin-top: 20px;
        font-size: 18px;
        color: #555;
    }
    .mobile-layout .product-item {
        padding: 10px;
        background-color: var(--color-white);
        box-sizing: border-box;
        min-height: auto;
    }

    .mobile-layout .xb-item--img img {
        width: 100%;
        height: auto;
    }







    .product-cart {
            border: 1px solid #ddd;
            /*border-radius: 0px;*/
            padding: 20px;
            max-width: 340px;
            height: 380px;
            margin: 16px auto;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            position: relative;
            background-color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .product-image {
            position: relative;
            width: 100px;
            height: 100px;
            /*overflow: hidden;*/
            /*border-radius: 0px;*/
            /*margin-top: 20px;*/
            margin: 25px auto 12px auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /*border-radius: 8px;*/
        }

        .sale-labels {
            position: absolute;
    top: 8px;
    right: 8px;
            display: block;
    background-color: #A02334;
    color: #fff;
    padding: 2px 6px;
    border-radius: 0px;
    font-weight: bold;
    text-transform: uppercase;
    font-size: 8px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.2);
    letter-spacing: 0.3px;
            text-align: center;
}

        .product-name {
            text-align: center;
            margin: 12px 0;
            font-size: 14px;
            color: #333;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            font-weight: bold
        }

        .out-of-stock {
            opacity: 0.8;
            color: #ff0000;
        }

        .add-to-cart-button {
    width: 100%;
    padding: 12px;
    background-color: #A02334;
    color: #fff;
    border: none;
    border-radius: 0px;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s;
    margin-top: auto;
    text-align: center;
    justify-content: center;
    align-self: center;
    display: flex;
    align-items: center;
    white-space: nowrap; /* Prevent text from wrapping */
}
    .add-to-cart-button:hover {
        background-color: #6C0311; /* Transparent background on hover */
        color: #FFFFFF; /* Change text color to the original button color */

    }

.add-to-cart-button-disabled {
    pointer-events: none; /* Disable the link */
    width: 100%;
    padding: 12px;
    background-color: grey;
    color: #fff;
    border: none;
    /*border-radius: 4px;*/
    font-size: 14px; /* Ensure consistent font size */
    cursor: not-allowed;
    transition: background-color 0.3s;
    margin-top: auto;
    align-items: center;
    justify-content: center;
    align-self: center;
    text-align: center;
    display: flex;
    align-items: center;
    white-space: nowrap; /* Prevent text from wrapping */
}

</style>

