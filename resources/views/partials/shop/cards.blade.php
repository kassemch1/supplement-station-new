@forelse($products as $item)
    @include('partials.shop.card', ['item' => $item, 'eager' => $loop->index < 4 && $products->currentPage() === 1])
@empty
    <div class="ss-empty">
        <div class="ss-empty__icon">{!! \App\Support\Icons::ui('search') !!}</div>
        <h2>No products found</h2>
        <p>Try another category, fewer filters or a different word.</p>
        <a href="{{ route('shop') }}" class="ss-btn ss-btn--dark" data-shop-link>See all products</a>
    </div>
@endforelse
