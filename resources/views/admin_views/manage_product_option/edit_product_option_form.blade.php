<!doctype html>
<html class="no-js" lang="en">



<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Manage Products Options</title>
    <meta name="robots" content="noindex, follow" />
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSS
	============================================ -->

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href={{asset("admin_assets/css/vendor/bootstrap.min.css")}}>

    <!-- Icon Font CSS -->
    <link rel="stylesheet" href={{asset("admin_assets/css/vendor/material-design-iconic-font.min.css")}}>
    <link rel="stylesheet" href={{asset("admin_assets/css/vendor/font-awesome.min.css")}}>
    <link rel="stylesheet" href={{asset("admin_assets/css/vendor/themify-icons.css")}}>
    <link rel="stylesheet" href={{asset("admin_assets/css/vendor/cryptocurrency-icons.css")}}>

    <!-- Plugins CSS -->
    <link rel="stylesheet" href={{asset("admin_assets/css/plugins/plugins.css")}}>

    <!-- Helper CSS -->
    <link rel="stylesheet" href={{asset("admin_assets/css/helper.css")}}>

    <!-- Main Style CSS -->
    <link rel="stylesheet" href={{asset("admin_assets/css/style.css")}}>

    <!-- Custom Style CSS Only For Demo Purpose -->
    <link id="cus-style" rel="stylesheet" href={{asset("admin_assets/css/style-primary.css")}}>

</head>

<body class="skin-dark">

<div class="main-wrapper">

    <!-- Header Section Start -->
    @include('partials/adminHeader')
    <!-- Header Section End -->

    <!-- Side Header Start -->
    @include('partials/adminSideBar')
    <!-- Side Header End -->

    <!-- Content Body Start -->
    <div class="content-body">

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

    </div><!-- Content Body End -->

    <!-- Footer Section Start -->
   
    @include('partials.adminFooter')
     
    <!-- Footer Section End -->

</div>

<!-- JS
============================================ -->

<!-- Global Vendor, plugins & Activation JS -->
<script src={{asset("admin_assets/js/vendor/modernizr-3.6.0.min.js")}}></script>
<script src={{asset("admin_assets/js/vendor/jquery-3.3.1.min.js")}}></script>
<script src={{asset("admin_assets/js/vendor/popper.min.js")}}></script>
<script src={{asset("admin_assets/js/vendor/bootstrap.min.js")}}></script>
<!--Plugins JS-->
<script src={{asset("admin_assets/js/plugins/perfect-scrollbar.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/tippy4.min.js.js")}}></script>
<!--Main JS-->
<script src={{asset("admin_assets/js/main.js")}}></script>

<!-- Plugins & Activation JS For Only This Page -->
<script src={{asset("admin_assets/js/plugins/nice-select/jquery.nice-select.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/nice-select/niceSelect.active.js")}}></script>
<script src={{asset("admin_assets/js/plugins/filepond/filepond.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/filepond/filepond-plugin-image-exif-orientation.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/filepond/filepond-plugin-image-preview.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/filepond/filepond.active.js")}}></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function () {
        $('#productOptionForm').submit(function (e) {
            e.preventDefault(); // Prevent default form submission

            // Serialize form data
            var formData = new FormData(this);

            // Send AJAX request
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
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


</body>



</html>
