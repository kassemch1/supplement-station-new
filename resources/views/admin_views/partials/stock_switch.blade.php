{{-- In stock / Out of stock switch for $product, saved as soon as it is flipped (admin-ui.js, [data-stock-toggle]) --}}
<label class="ad-stock {{ (int) $product->stock === 1 ? 'is-in' : 'is-out' }}" title="Flip to change the stock">
    <input type="checkbox" data-stock-toggle="{{ route('manageProducts.stock', ['id' => $product->id]) }}" @checked((int) $product->stock === 1)
           aria-label="{{ $product->name }} is in stock">
    <span class="ad-stock__track" aria-hidden="true"></span>
    <span class="ad-stock__label" data-in="In Stock" data-out="Out of Stock">{{ (int) $product->stock === 1 ? 'In Stock' : 'Out of Stock' }}</span>
</label>
