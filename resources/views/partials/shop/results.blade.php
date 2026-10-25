{{-- Toolbar, product grid and "load more". Sent again by the shop script after every filter change. --}}
@php
    use App\Support\Icons;
    use App\Support\ShopUrl;
    $chips = [];
    foreach ($filters['brands'] as $brand) {
        $chips[] = [$brand->name, ShopUrl::toggleBrand($baseUrl, $brand->slug)];
    }
    if ($filters['min'] !== null || $filters['max'] !== null) {
        $chips[] = ['$' . ($filters['min'] !== null ? (int) $filters['min'] : 0) . ' – ' . ($filters['max'] !== null ? '$' . (int) $filters['max'] : 'any'), ShopUrl::with($baseUrl, ['min' => null, 'max' => null])];
    }
    if ($filters['in_stock']) $chips[] = ['In stock', ShopUrl::with($baseUrl, ['in_stock' => null])];
    if ($filters['sale']) $chips[] = ['On sale', ShopUrl::with($baseUrl, ['sale' => null])];
    if ($search !== null) $chips[] = ['“' . $search . '”', ShopUrl::with($baseUrl, ['q' => null, 'shop-search' => null])];
    $activeCount = count($chips);
@endphp
<div class="ss-toolbar">
    <p class="ss-toolbar__count">
        @if($totalProducts)
            <strong data-shown>{{ $products->lastItem() }}</strong> of {{ $totalProducts }} {{ \Illuminate\Support\Str::plural('product', $totalProducts) }}
        @else
            No products
        @endif
    </p>
    <div class="ss-toolbar__actions">
        <button type="button" class="ss-pillbtn ss-only-mobile" data-open-filters>
            {!! Icons::ui('filters') !!} Filters
            @if($activeCount)<span class="ss-badge">{{ $activeCount }}</span>@endif
        </button>
        <form action="{{ $baseUrl }}" method="get" class="ss-sort" data-shop-form>
            @foreach(\Illuminate\Support\Arr::except(ShopUrl::current(), ['orderby']) as $name => $value)
                <input type="hidden" name="{{ $name }}" value="{{ $value }}">
            @endforeach
            <label class="ss-sort__label" for="shop-sort">Sort</label>
            <select name="orderby" id="shop-sort" data-autosubmit>
                @foreach($sorts as $value => $label)
                    <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
            {!! Icons::ui('down', 'ss-ui ss-sort__chev') !!}
        </form>
    </div>
</div>

@if($chips)
    <div class="ss-chips">
        @foreach($chips as [$label, $url])
            <a href="{{ $url }}" class="ss-chip" data-shop-link>{{ $label }} {!! Icons::ui('x') !!}</a>
        @endforeach
        <a href="{{ $baseUrl }}" class="ss-chip ss-chip--clear" data-shop-link>Clear all</a>
    </div>
@endif

<div class="ss-grid" id="shop-grid">
    @include('partials.shop.cards')
</div>

@if($products->hasMorePages())
    {{-- A real link to the next page, so search engines still reach every product; the script loads it in place --}}
    <div class="ss-more">
        <a href="{{ $products->nextPageUrl() }}" class="ss-btn ss-btn--outline" data-load-more>Load more products</a>
    </div>
@endif
