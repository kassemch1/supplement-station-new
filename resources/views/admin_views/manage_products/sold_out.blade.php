@extends('layouts.admin')

@section('title', 'Sold Out Products')

@php
    $money = fn ($v) => '$' . number_format((float) $v, 2);
    $zone = config('app.display_timezone');
    $recentDays = \App\Services\SoldOutProducts::RECENT_DAYS;
    $filtered = $filters['search'] !== '' || $filters['category'] || $filters['brand'];
@endphp

@section('content')
        <div class="row justify-content-between align-items-center mb-10">
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Sold Out <span class="so-count" data-soldout-total>{{ $total }}</span></h3>
                    <p class="text-muted mb-0">Products customers can't buy right now. The ones that sold most in the last {{ $recentDays }} days come first: restock those first. Flip the switch when a product is back.</p>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-head">
                <form action="{{ route('manageProducts.soldOut') }}" method="GET" class="form-inline so-filter" data-ajax-filter>
                    <input type="search" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="Search a product" aria-label="Search sold-out products by name" autocomplete="off">
                    <select name="category" class="form-control" aria-label="Category">
                        <option value="">All categories</option>
                        @foreach($categories as $main)
                            <option value="{{ $main->id }}" @selected($filters['category'] === $main->id)>{{ $main->name }}</option>
                            @foreach($main->children as $child)
                                <option value="{{ $child->id }}" @selected($filters['category'] === $child->id)>&nbsp;&nbsp;↳ {{ $child->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                    <select name="brand" class="form-control" aria-label="Brand">
                        <option value="">All brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" @selected($filters['brand'] === $brand->id)>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                    <select name="sort" class="form-control" data-clear-to="best" aria-label="Order of the list">
                        @foreach(\App\Services\SoldOutProducts::SORTS as $key => $label)
                            <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="box-body" data-ajax-list="sold-out">
                @if($filtered)
                    <p class="so-found">{{ $products->total() }} of {{ $total }} sold-out products match</p>
                @endif
                <table class="table so-table">
                    <thead>
                        <tr>
                            <th></th>
                            <th>Product</th>
                            <th>Category</th>
                            <th class="text-right">Price</th>
                            <th>Sold out since</th>
                            <th class="text-right">Sold in {{ $recentDays }} days</th>
                            <th>Stock</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            @php
                                $since = $product->sold_out_at ?? $product->updated_at;
                                $edit = route('manageProducts.edit', ['id' => $product->id]);
                            @endphp
                            <tr class="so-row" data-soldout-row>
                                <td class="so-img">
                                    <a href="{{ $edit }}" aria-label="Edit {{ $product->name }}">
                                        @if($product->images->isNotEmpty())
                                            <img src="{{ asset($product->images->first()->url) }}" alt="" loading="lazy" width="48" height="48">
                                        @endif
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ $edit }}" class="so-name">{{ $product->name }}</a>
                                    @if($product->brand)
                                        <small class="so-brand">{{ $product->brand->name }}</small>
                                    @endif
                                </td>
                                <td>{{ $product->getCategory?->name ?? '—' }}</td>
                                <td class="text-right">{{ $money($product->price_after_discount) }}</td>
                                <td>
                                    {{ $since?->timezone($zone)->format('M j, Y') }}
                                    <small class="so-ago">{{ $since?->diffForHumans(['parts' => 1]) }}</small>
                                </td>
                                <td class="text-right">
                                    @if($product->sold_recently > 0)
                                        <strong class="so-sold">{{ $product->sold_recently }}</strong>
                                    @else
                                        <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td>@include('admin_views.partials.stock_switch', ['product' => $product])</td>
                                <td class="text-right">
                                    <a href="{{ $edit }}" class="button button-info">Edit</a>
                                    <a href="{{ $product->url() }}" class="button button-outline button-secondary" target="_blank" rel="noopener">View</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="ad-empty">
                                {{ $filtered ? 'No sold-out products match these filters.' : 'Nothing is sold out. Every product in the shop can be bought.' }}
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-center">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
@endsection

@push('styles')
<style>
    .so-count { display: inline-block; min-width: 28px; padding: 2px 9px; margin-left: 6px; border-radius: 999px; background: rgba(220, 53, 69, .15); color: #e5606d; font-size: 15px; text-align: center; vertical-align: middle; }
    .so-filter { gap: 8px; flex-wrap: wrap; }
    .so-found { margin: 0 0 10px; opacity: .7; font-size: 13px; }
    .so-table td { vertical-align: middle; }
    .so-img { width: 64px; }
    .so-img img { width: 48px; height: 48px; object-fit: contain; border-radius: 8px; background: #fff; }
    .so-name { font-weight: 600; }
    .so-brand, .so-ago { display: block; opacity: .65; font-size: 12px; }
    .so-sold { color: #f0a33a; }
    .so-row.is-back { opacity: .45; }
</style>
@endpush

@push('scripts')
<script>
    // A product switched back in stock leaves the list (unless it is switched off again within a moment)
    $(document).on('stock:changed', '[data-soldout-row] [data-stock-toggle]', function (e, inStock) {
        var $row = $(this).closest('[data-soldout-row]');
        $row.toggleClass('is-back', inStock);
        if (!inStock) {
            return;
        }
        setTimeout(function () {
            if (!$row.hasClass('is-back')) {
                return;
            }
            $row.fadeOut(200, function () {
                $row.remove();
                var $count = $('[data-soldout-total]');
                $count.text(Math.max(0, parseInt($count.text(), 10) - 1));
            });
        }, 1500);
    });
</script>
@endpush
