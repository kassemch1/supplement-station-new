@extends('layouts.admin')

@section('title', 'Manage Brands')

@section('content')
        <div class="row justify-content-between align-items-center mb-10">
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Manage Brands</h3>
                </div>
            </div>
        </div>

        <div class="add-edit-product-wrap col-12">
            <div class="add-edit-product-form">
                <form id="brandForm" method="post" enctype="multipart/form-data"
                      action="{{ $brand->exists ? route('manageBrands.update', ['id' => $brand->id]) : route('manageBrands.store') }}">
                    @csrf
                    <h4 class="title">{{ $brand->exists ? 'Edit Brand' : 'Add Brand' }}</h4>

                    <div class="row">
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="brand_name">Name</label>
                            <input id="brand_name" name="name" class="form-control" type="text" placeholder="e.g. Optimum Nutrition" value="{{ $brand->name }}">
                        </div>
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="brand_order">Order in the shop <small class="text-muted">(lower first, optional)</small></label>
                            <input id="brand_order" name="sort_order" class="form-control" type="number" min="0" value="{{ $brand->sort_order ?? 0 }}">
                        </div>
                        <div class="col-lg-6 col-12 mb-30">
                            <label>Logo <small class="text-muted">(optional; saved as a small WebP)</small></label>
                            @if($brand->logoUrl())
                                <div class="mb-10" style="display:flex;align-items:center;gap:14px;">
                                    <span style="display:inline-grid;place-items:center;width:120px;height:64px;background:#fff;border-radius:8px;padding:6px;">
                                        <img src="{{ $brand->logoUrl() }}" alt="" style="max-width:100%;max-height:100%;object-fit:contain;">
                                    </span>
                                    <label class="adomx-checkbox"><input type="checkbox" name="remove_logo" value="1"> <i class="icon"></i> Remove logo</label>
                                </div>
                            @endif
                            <input class="dropify" type="file" name="logo" accept="image/png,image/jpeg,image/webp">
                        </div>
                    </div>

                    <div id="successAlert" class="alert alert-success mt-3" style="display: none;"></div>
                    <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;"></div>

                    <div class="row">
                        <div class="d-flex flex-wrap justify-content-end col mbn-10">
                            <button class="button button-outline button-primary mb-10 ml-10 mr-0" type="submit" id="submitBtn">Save & Publish</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
@endsection

@push('scripts')
<script>
    $('#brandForm').on('submit', function (e) {
        e.preventDefault();
        var $button = $('#submitBtn').prop('disabled', true);
        $.ajax({type: 'POST', url: this.action, data: new FormData(this), processData: false, contentType: false})
            .done(function (response) {
                $('#errorAlert').hide();
                $('#successAlert').text(response.message).fadeIn();
                setTimeout(function () { window.location.href = response.redirect; }, 900);
            })
            .fail(function (xhr) {
                var json = xhr.responseJSON || {};
                var message = json.errors ? Object.values(json.errors)[0][0] : (json.message || 'Could not save the brand.');
                $('#errorAlert').text(message).fadeIn();
                $button.prop('disabled', false);
            });
    });
</script>
@endpush
