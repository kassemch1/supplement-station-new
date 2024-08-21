<div class="d-flex justify-content-center" id="pagination-container">
    {!! $product->appends(request()->query())->links() !!}
</div>
