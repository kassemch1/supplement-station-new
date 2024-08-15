
@if($agent->isMobile())
    <!-- Mobile Layout -->
    <div class="row">
        @foreach($product as $item)
            <div class="col-6">
                <div class="product-cart">
                    <!-- Sale Label -->
                    @if($item->discount > 0)
                        <span class="sale-labels">On Sale</span>
                    @endif
            
                    <!-- Product Image -->
                    <a href="{{ route('products.show', $item->id) }}" class="product-image">
                        @if($item->images->isNotEmpty())
                            <img src="{{ asset($item->images->first()->url) }}" alt="Product Image">
                        @else
                            <span style="
                                display: block;
                                width: 100%;
                                height: 100%;
                                background-color: #f0f0f0; /* Placeholder color */
                                border-radius: 8px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: #888;
                            ">No image available</span>
                        @endif
                    </a>
            
                    
                    <!-- Product Name -->
                    @if($item->stock > 0)
                        <a href="{{ route('products.show', $item->id) }}" class="product-name">
                            {{ $item->name }}
                        </a>
                    @else
                        <span class="product-name">
                            {{ $item->name }}<br/>
                            <span class="out-of-stock">(out of stock)</span>
                        </span>
                    @endif
                    <div >
                        @if($item->discount > 0)
                            <span style="text-decoration: line-through; color: gray;font-size:12px">${{ number_format($item->price, 2) }}</span>
                            <span style="color: #A02334;">${{ number_format($item->price - ($item->price * $item->discount / 100), 2) }}</span>
                        @else
                            ${{ number_format($item->price, 2) }}
                        @endif
                    </div>
                    
                    @if($item->stock > 0)
                    <!-- Add to Cart Button -->
                    <a class="add-to-cart-button" href="{{ route('products.show', $item->id) }}">
                        Add to Cart
                    </a>
                    @else
                    <a class="add-to-cart-button-disabled" >
                        Add to Cart
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
            <div class="col-lg-4 col-md-4 col-sm-6 col-12">
                <div class="product product-item text-center"
                     style="position: relative; overflow: hidden; min-height:450px">
                    @if($item->discount > 0)
                    <div class="ribbon" style="
                    width: 150px;
                    height: 150px;
                    overflow: hidden;
                    position: absolute;
                    top: -10px;
                    right: 0px;
                    z-index: 2;
                ">
                    <span style="
                        position: absolute;
                        display: block;
                        width: 225px;
                        padding: 15px 0;
                        background-color: #A02334;
                        color: white;
                        text-transform: uppercase;
                        font-weight: bold;
                        text-align: center;
                        transform: rotate(45deg);
                        top: 30px;
                        right: -65px;
                    ">On Sale</span>
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
            border-radius: 8px; 
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
            width: 80px; 
            height: 140px; 
            overflow: hidden; 
            border-radius: 8px;
            margin-bottom: 12px;
            margin: 0 auto 12px auto;
            display: flex;
            align-items: center;
            justify-content: center; 
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }

        .sale-labels {
            position: absolute;
    top: 8px;
    right: 8px;
    background-color: #A02334;
    color: #fff; 
    padding: 2px 6px; 
    border-radius: 8px;
    font-weight: bold; 
    text-transform: uppercase; 
    font-size: 8px; 
    box-shadow: 0 1px 2px rgba(0,0,0,0.2); 
    letter-spacing: 0.3px; 
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
            border-radius: 4px; 
            font-size: 16px; 
            cursor: pointer; 
            transition: background-color 0.3s; 
            margin-top: auto; 
            text-align: center
        }

        .add-to-cart-button-disabled {
            width: 100%; 
            padding: 12px; 
            background-color: grey; 
            color: #fff; 
            border: none; 
            border-radius: 4px; 
            font-size: 16px; 
            cursor: pointer; 
            transition: background-color 0.3s; 
            margin-top: auto; 
            align-items: center;
            justify-content: center;
            align-self: center;
            text-align: center
        }
</style>

