@extends('layouts.admin')

@section('title', 'Manage Products')

@section('content')
        <!-- Page Headings Start -->
        <div class="row justify-content-between align-items-center mb-10">

            <!-- Page Heading Start -->
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Manage Products</h3>
                </div>
            </div><!-- Page Heading End -->

        </div><!-- Page Headings End -->

        <!-- Add or Edit Product Start -->

        <div class="add-edit-product-wrap col-12">

            <div class="add-edit-product-form">
                <form id="productForm" method="post" action="{{route('manageProducts.store')}}" enctype="multipart/form-data">
                    @csrf
                    <h4 class="title">Add Product</h4>

                    <div class="row">
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Name</label>
                            <input name="name" class="form-control" type="text"  placeholder="Product Name">
                            @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Price</label>
                            <input name="price" class="form-control" type="text"  placeholder="Product Price" >
                            @if ($errors->has('price'))
                                <span class="text-danger">{{ $errors->first('price') }}</span>
                            @endif
                        </div>
                        @include('admin_views.manage_products._category_brand', ['product' => $product ?? null])
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Discount</label>
                            <input name="discount" class="form-control" type="text"  placeholder="Product Discount">
                            @if ($errors->has('discount'))
                                <span class="text-danger">{{ $errors->first('discount') }}</span>
                            @endif
                        </div>

                        <div class="col-12 mb-30">
                            <label for="">Description</label>
                            <textarea name="description" class="form-control"></textarea>
                            @if ($errors->has('description'))
                                <span class="text-danger">{{ $errors->first('description') }}</span>
                            @endif
                        </div>

                        <div class="col-12 mb-30">
                            <label>Product Picture(s)</label>
                            <input class="dropify" type="file" name="images[]" multiple>
                            <label class="adomx-checkbox mt-10"><input type="checkbox" name="keep_background" value="1"> <i class="icon"></i> Keep the original background <small class="text-muted">(by default a plain white background is removed)</small></label>
                            @if ($errors->has('images'))
                                <span class="text-danger">{{ $errors->first('images') }}</span>
                            @endif
                        </div>
                        <div class="col-lg-6 col-12 mb-30">
                            <label>Stock</label>
                            <div class="adomx-checkbox-radio-group">
                                <label class="adomx-radio"><input type="radio" name="stock" value="in-stock"> <i class="icon"></i> In Stock</label>
                                <label class="adomx-radio"><input type="radio" name="stock" value="out-of-stock"> <i class="icon"></i> Out of Stock</label>
                            </div>
                            @if ($errors->has('stock'))
                                <span class="text-danger">{{ $errors->first('stock') }}</span>
                            @endif
                        </div>

                    </div>
                    <!-- Success Alert -->
                    <div id="successAlert" class="alert alert-success mt-3" style="display: none;">
                        <strong>Success!</strong> Product added successfully.
                    </div>
                    <!-- Error Alert -->
                    <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;">
                        <strong>Error!</strong> Failed to add product. Please try again.
                    </div>
                    <!-- Button Group Start -->
                    <div class="row">
                        <div class="d-flex flex-wrap justify-content-end col mbn-10">
                            <button class="button button-outline button-primary mb-10 ml-10 mr-0" type="submit" id="submitBtn">Save & Publish</button>
                        </div><!-- Button Group End -->
                    </div>
                </form>
            </div>

        </div><!-- Add or Edit Product End -->
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#productForm').submit(function (e) {
            e.preventDefault(); // Prevent default form submission

            // Serialize form data
            var formData = new FormData(this);

            // Send AJAX request
            $.ajax({
                type: 'POST',
                url: $(this).attr('action'), // Use form's action attribute as URL
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    $('#successAlert').fadeIn();
                    setTimeout(function () {
                        $('#successAlert').fadeOut();
                    }, 3000);
                    $('.text-danger').remove(); // Remove existing error messages
                },
                error: function (xhr, status, error) {
                    $('#errorAlert').fadeIn();
                    setTimeout(function () {
                        $('#errorAlert').fadeOut();
                    }, 3000);
                    let errors = xhr.responseJSON.errors;
                    $('.text-danger').remove(); // Remove existing error messages

                    // Loop through the errors and display them
                    $.each(errors, function(field, messages) {
                        // Handle radio button errors
                        if (field === 'stock') {
                            let inputField = $('[name="stock"]').last(); // Select the last radio button for proper placement
                            inputField.closest('.adomx-checkbox-radio-group').after('<span class="text-danger">' + messages[0] + '</span>');
                        }
                        // Handle image input errors
                        else if (field.startsWith('images')) {
                            let inputField = $('[name="images[]"]');
                            inputField.after('<span class="text-danger">' + messages[0] + '</span>');
                        }
                        // Handle other fields
                        else {
                            let inputField = $('[name="' + field + '"]');
                            inputField.after('<span class="text-danger">' + messages[0] + '</span>');
                        }
                    });
                }

            });
        });
    });
</script>
@endpush
