{{-- Filters: a column beside the products on wide screens, a sheet that slides up on phones --}}
@php
    use App\Support\Icons;
    use App\Support\ShopUrl;
    $chosenBrands = $filters['brands']->pluck('slug')->all();
@endphp
<div class="ss-filters">
    <div class="ss-filters__head">
        <strong>Filters</strong>
        <button type="button" class="ss-iconbtn" data-close-filters aria-label="Close filters">{!! Icons::ui('x') !!}</button>
    </div>

    <form class="ss-search" action="{{ $baseUrl }}" method="get" data-shop-form role="search">
        @foreach(\Illuminate\Support\Arr::except(ShopUrl::current(), ['q', 'shop-search']) as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        {!! Icons::ui('search') !!}
        <input type="search" name="q" value="{{ $search }}" placeholder="{{ $category ? 'Search in ' . $category->name : 'Search products or brands' }}" aria-label="Search products">
    </form>

    <div class="ss-fgroup">
        <div class="ss-fgroup__title">Show only</div>
        <a href="{{ ShopUrl::with($baseUrl, ['in_stock' => $filters['in_stock'] ? null : 1]) }}" data-shop-link
           class="ss-toggle {{ $filters['in_stock'] ? 'is-on' : '' }}" role="switch" aria-checked="{{ $filters['in_stock'] ? 'true' : 'false' }}">
            <span class="ss-toggle__knob"></span> In stock
        </a>
        <a href="{{ ShopUrl::with($baseUrl, ['sale' => $filters['sale'] ? null : 1]) }}" data-shop-link
           class="ss-toggle {{ $filters['sale'] ? 'is-on' : '' }}" role="switch" aria-checked="{{ $filters['sale'] ? 'true' : 'false' }}">
            <span class="ss-toggle__knob"></span> On sale
        </a>
    </div>

    <form class="ss-fgroup ss-price" action="{{ $baseUrl }}" method="get" data-shop-form>
        <div class="ss-fgroup__title">Price (USD)</div>
        @foreach(\Illuminate\Support\Arr::except(ShopUrl::current(), ['min', 'max']) as $name => $value)
            <input type="hidden" name="{{ $name }}" value="{{ $value }}">
        @endforeach
        <div class="ss-price__row">
            <label><span>From</span><input type="number" name="min" min="0" step="1" inputmode="numeric" placeholder="{{ $priceRange[0] }}" value="{{ $filters['min'] !== null ? (int) $filters['min'] : '' }}"></label>
            <label><span>To</span><input type="number" name="max" min="0" step="1" inputmode="numeric" placeholder="{{ $priceRange[1] }}" value="{{ $filters['max'] !== null ? (int) $filters['max'] : '' }}"></label>
            <button type="submit" class="ss-btn ss-btn--dark ss-btn--sm">Go</button>
        </div>
    </form>

    @if($brands->isNotEmpty())
        <div class="ss-fgroup">
            <div class="ss-fgroup__title">Brands</div>
            @if($brands->count() > 8)
                <input type="search" class="ss-brandfind" placeholder="Find a brand" aria-label="Find a brand" data-brand-find>
            @endif
            <ul class="ss-brandlist">
                @foreach($brands as $brand)
                    @php $on = in_array($brand->slug, $chosenBrands, true); @endphp
                    <li data-brand-name="{{ strtolower($brand->name) }}">
                        <a href="{{ ShopUrl::toggleBrand($baseUrl, $brand->slug) }}" data-shop-link class="{{ $on ? 'is-on' : '' }}">
                            <span class="ss-check">{!! Icons::ui('check') !!}</span>
                            <span class="ss-brandlist__logo">
                                @if($brand->logoUrl())
                                    <img src="{{ $brand->logoUrl() }}" alt="" loading="lazy">
                                @else
                                    <span class="ss-mono">{{ $brand->initials() }}</span>
                                @endif
                            </span>
                            <span class="ss-brandlist__name">{{ $brand->name }}</span>
                            <span class="ss-count">{{ $brandCounts[$brand->id] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ss-filters__foot">
        <a href="{{ $baseUrl }}" class="ss-btn ss-btn--ghost" data-shop-link>Clear all</a>
        <button type="button" class="ss-btn ss-btn--red" data-close-filters>Show {{ $totalProducts }} {{ \Illuminate\Support\Str::plural('product', $totalProducts) }}</button>
    </div>
</div>
