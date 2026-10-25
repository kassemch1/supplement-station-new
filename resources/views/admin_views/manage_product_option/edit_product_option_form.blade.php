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
<!-- admin_views/manage_product_option/edit_product_option.blade.php -->

<div class="add-edit-product-wrap col-12">
    <div class="add-edit-product-form">
        <form id="productOptionForm" method="post" action="{{ route('manageProductOptions.update', ['id' => $productOption->id]) }}" enctype="multipart/form-data">

            @csrf
            @method('PUT')
            <input type="hidden" name="id" value="{{$productOption->id}}">
            <h4 class="title">Edit Product Option</h4>

            <div class="row">
                <div class="col-lg-6 col-12 mb-30">
                    <label for="">Option</label>
                    <select name="option_id" class="form-control select2">
                        @foreach($options as $option)
                            <option value="{{$option->id}}" {{ $option->id == $productOption->option_id ? 'selected' : '' }}>{{$option->option_name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-6 col-12 mb-30">
                    <label for="">Option Value</label>
                    <input name="option_value" class="form-control" type="text" placeholder="Option Value" value="{{$productOption->option_value}}">
                </div>
                <div class="col-12 mb-30">
                    <label>Stock</label>
                    <div class="adomx-checkbox-radio-group">
                        <label class="adomx-radio"><input type="radio" name="stock" value="1" {{ $productOption->stock ? 'checked' : '' }}> <i class="icon"></i> In Stock</label>
                        <label class="adomx-radio"><input type="radio" name="stock" value="0" {{ !$productOption->stock ? 'checked' : '' }}> <i class="icon"></i> Out of Stock</label>
                    </div>
                </div>
            </div>

            <!-- Success Alert -->
            <div id="successAlert" class="alert alert-success mt-3" style="display: none;">
                <strong>Success!</strong> Product option updated successfully.
            </div>
            <!-- Error Alert -->
            <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;">
                <strong>Error!</strong> Failed to update product option. Please try again.
            </div>

            <!-- Button Group Start -->
            <div class="row">
                <div class="d-flex flex-wrap justify-content-end col mbn-10">
                    <button class="button button-outline button-primary mb-10 ml-10 mr-0" type="submit" id="submitBtn">Save & Publish</button>
                </div>
            </div><!-- Button Group End -->
        </form>
    </div>
</div><!-- Add or Edit Product Option End -->
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#productOptionForm').submit(function (e) {
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
