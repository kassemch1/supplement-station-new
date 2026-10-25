@extends('layouts.admin')

@section('title', 'Manage Products Options')

@section('content')
        <!-- Page Headings Start -->
        <div class="row justify-content-between align-items-center mb-10">

            <!-- Page Heading Start -->
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Manage Product Options</h3>
                </div>
            </div><!-- Page Heading End -->

        </div><!-- Page Headings End -->

        <!-- Add or Edit Product Start -->
        <div class="add-edit-product-wrap col-12">
            <div class="add-edit-product-form">
                <form id="productOptionForm" method="post" action="{{ route('manageProductsOptions.store') }}" enctype="multipart/form-data">
                    @csrf
                    <h4 class="title">Add Product Option</h4>

                    <div class="row">
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Option</label>
                            <select name="option_id" class="form-control select2">
                                @foreach($options as $option)
                                    <option value="{{ $option->id }}">{{ $option->option_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Option Value</label>
                            <input name="option_value" class="form-control" type="text" placeholder="Option Value">
                        </div>
                        <div class="col-lg-6 col-12 mb-30">
                            <label>Stock</label>
                            <div class="adomx-checkbox-radio-group">
                                <label class="adomx-radio"><input type="radio" name="stock" value="1" checked> <i class="icon"></i> In Stock</label>
                                <label class="adomx-radio"><input type="radio" name="stock" value="0"> <i class="icon"></i> Out of Stock</label>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12 mb-30">
                            <label>Picture <small class="text-muted">(optional: the flavour tub, the colour... saved as a small WebP)</small></label>
                            @if(isset($productOption) && $productOption->imageUrl())
                                @include('admin_views.partials.removable_image', [
                                    'src' => $productOption->imageUrl(), 'url' => route('manageProductOptions.removeImage', $productOption->id),
                                    'confirm' => 'Remove this option\'s picture?',
                                ])
                            @endif
                            <input class="dropify" type="file" name="image" accept="image/png,image/jpeg,image/webp">
                            <label class="adomx-checkbox mt-10"><input type="checkbox" name="keep_background" value="1"> <i class="icon"></i> Keep the original background <small class="text-muted">(by default a plain white background is removed)</small></label>
                        </div>
                        <input type="hidden" name="product_id" value="{{ $product_id }}">
                    </div>

                    <div id="successAlert" class="alert alert-success mt-3" style="display: none;">
                        <strong>Success!</strong> Product option added successfully.
                    </div>

                    <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;">
                        <strong>Error!</strong> Failed to add product option. Please try again.
                    </div>

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
    $(document).ready(function () {
        $('#productOptionForm').submit(function (e) {
            e.preventDefault();

            var formData = new FormData(this);

            $.ajax({
                type: 'POST',
                url: $(this).attr('action'),
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#successAlert').fadeIn();
                    setTimeout(function () {
                        $('#successAlert').fadeOut();
                    }, 3000);
                },
                error: function (xhr, status, error) {
                    $('#errorAlert').fadeIn();
                    setTimeout(function () {
                        $('#errorAlert').fadeOut();
                    }, 3000);
                }
            });
        });
    });
</script>
@endpush
