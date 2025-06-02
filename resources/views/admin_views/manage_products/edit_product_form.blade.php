<!doctype html>
<html class="no-js" lang="en">



<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Manage Products</title>
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
                    <h3>Manage Products</h3>
                </div>
            </div><!-- Page Heading End -->

        </div><!-- Page Headings End -->

        <!-- Add or Edit Product Start -->

        <div class="add-edit-product-wrap col-12">

            <div class="add-edit-product-form">
                <form id="productForm" method="post" action="{{route('manageProducts.update')}}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="product_id" value="{{$product->id}}">
                    <h4 class="title">Edit Product</h4>

                    <div class="row">
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Name</label>
                            <input name="name" class="form-control" type="text"  placeholder="Product Name" value="{{$product->name}}">
                            @if ($errors->has('name'))
                                <span class="text-danger">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Price</label>
                            <input name="price" class="form-control" type="text"  placeholder="Product Price" value="{{$product->price}}">
                            @if ($errors->has('price'))
                                <span class="text-danger">{{ $errors->first('price') }}</span>
                            @endif
                        </div>
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Category</label>
                            <select name="category_id" class="form-control select2">
                                @foreach($categories as $category)
                                    <option value="{{$category->id}}" {{ $category->id == $product->category_id ? 'selected' : '' }}>{{$category->name}}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('category_id'))
                                <span class="text-danger">{{ $errors->first('category_id') }}</span>
                            @endif
                        </div>
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Discount</label>
                            <input name="discount" class="form-control" type="text"  placeholder="Product Discount" value="{{$product->discount}}">
                            @if ($errors->has('discount'))
                                <span class="text-danger">{{ $errors->first('discount') }}</span>
                            @endif
                        </div>

                        <div class="col-12 mb-30">
                            <label for="">Description</label>
                            <textarea name="description" class="form-control">{{$product->description}}</textarea>
                            @if ($errors->has('description'))
                                <span class="text-danger">{{ $errors->first('description') }}</span>
                            @endif
                        </div>

                        <div class="col-12 mb-30">
                            <label>Product Picture(s)</label>
                            <input class="dropify" type="file" name="images[]" multiple>
                            @if ($errors->has('images'))
                                <span class="text-danger">{{ $errors->first('images') }}</span>
                            @endif
                        </div>
                        <div style="display: flex; flex-wrap: wrap; gap: 20px; justify-content: space-between; align-items: flex-start;">
                            @foreach($product->images as $image)
                                <div style="flex: 1 1 100px; box-sizing: border-box;">
                                    <img src="{{ asset($image->url) }}" alt="img" style="width: 100px; height: 100px; object-fit: cover; display: block; margin-bottom: 10px;">
                                    <a class="button button-danger"
                                       href="{{route("productImage.destroy",['id'=>$image->id])}}"><span>Delete</span></a>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-12 mb-30">
                            <label>Stock</label>
                            <div class="adomx-checkbox-radio-group">
                                <label class="adomx-radio"><input type="radio" name="stock" value="in-stock" {{ $product->stock ? 'checked' : '' }}> <i class="icon"></i> In Stock</label>
                                <label class="adomx-radio"><input type="radio" name="stock" value="out-of-stock" {{ !$product->stock ? 'checked' : '' }}> <i class="icon"></i> Out of Stock</label>
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
        $('#productForm').submit(function (e) {
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


</body>



</html>
