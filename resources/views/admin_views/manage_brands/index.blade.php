@extends('layouts.admin')

@section('title', 'Manage Brands')

@section('content')
        <div class="row justify-content-between align-items-center mb-10">
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Manage Brands</h3>
                </div>
            </div>
            <div class="col-12 col-lg-auto mb-20">
                <a class="button button-primary" href="{{ route('manageBrands.create') }}"><span>Add Brand</span></a>
            </div>
        </div>

        <div class="col-12 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Brands</h4>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th style="width: 110px;">Logo</th>
                            <th>Brand</th>
                            <th>Products</th>
                            <th>Order</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($brands as $brand)
                            <tr>
                                <td>
                                    @if($brand->logoUrl())
                                        <span style="display:inline-grid;place-items:center;width:90px;height:48px;background:#fff;border-radius:8px;padding:4px;">
                                            <img src="{{ $brand->logoUrl() }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                                        </span>
                                    @else
                                        <span class="text-muted">No logo</span>
                                    @endif
                                </td>
                                <td>{{ $brand->name }}</td>
                                <td>{{ $brand->products_count }}</td>
                                <td>{{ $brand->sort_order }}</td>
                                <td>
                                    <a class="button button-info" href="{{ route('manageBrands.edit', ['id' => $brand->id]) }}"><span>Edit</span></a>
                                    <button type="button" class="button button-danger" data-delete-brand="{{ route('manageBrands.destroy', ['id' => $brand->id]) }}"
                                            data-name="{{ $brand->name }}" data-products="{{ $brand->products_count }}"><span>Delete</span></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5">No brands yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '[data-delete-brand]', function () {
        var products = parseInt($(this).data('products'), 10);
        var question = 'Delete the brand "' + $(this).data('name') + '"?' +
            (products ? '\n\nIts ' + products + ' product(s) stay in the shop, just without a brand.' : '');
        if (!confirm(question)) {
            return;
        }
        $.ajax({type: 'DELETE', url: $(this).data('delete-brand'), data: {_token: '{{ csrf_token() }}'}})
            .done(function () { location.reload(); })
            .fail(function (xhr) { alert((xhr.responseJSON && xhr.responseJSON.message) || 'Could not delete the brand.'); });
    });
</script>
@endpush
