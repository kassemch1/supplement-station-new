<!doctype html>
<html class="no-js" lang="en">


<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Manage Products</title>
    <meta name="robots" content="noindex, follow"/>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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
    @include('partials.adminHeader')
    <!-- Header Section End -->

    <!-- Side Header Start -->
    @include('partials.adminSideBar')
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

<!-- Combined Filter Form -->
<form method="GET" action="{{ route('manageProducts.index') }}" class="mb-3">
    <!-- Search Bar -->
    <input type="text" name="search" placeholder="Search for products..." value="{{ request('search') }}" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px;"/>

    <!-- Dropdown for Categories -->
    <select name="category_id" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; margin-right: 10px;">
        <option value="">Select Category</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
        @endforeach
    </select>

    <!-- Dropdown for Stock Status -->
    <select name="stock" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; margin-right: 10px;">
        <option value="">Select Stock Status</option>
        <option value="in-stock" {{ request('stock') == 'in-stock' ? 'selected' : '' }}>In Stock</option>
        <option value="out-of-stock" {{ request('stock') == 'out-of-stock' ? 'selected' : '' }}>Out of Stock</option>
    </select>

    <button type="submit" style="padding: 5px 10px; border: none; background-color: #17A2B8; color: white; border-radius: 4px; cursor: pointer;">Filter</button>
</form>


<div class="col-lg-6 col-12 mb-30">
    <div class="box">
        <div class="box-head">
            <h4 class="title">Products</h4>
        </div>
        <div class="box-body">

            <!-- Products Table -->
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Discount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @foreach($products as $product)
                        <tr>
                            <td>
                                @if($product->images->isNotEmpty())
                                    <img src="{{ asset($product->images->first()->url) }}" alt="img">
                                @else
                                    No image available
                                @endif
                            </td>
                            <td style="white-space: pre-wrap; word-wrap: break-word;">{{ $product->name }}</td>
                            <td>{{ $product->price }} $</td>
                            <td style="max-width: 250px" class="text-truncate">{{ $product->description }}</td>
                            <td>{{ $product->getCategory->name }}</td>
                            <td>
                                @if((int)$product->stock === 1)
                                    <span class="badge badge-success">In Stock</span>
                                @else
                                    <span class="badge badge-danger">Out of Stock</span>
                                @endif
                            </td>
                            <td>{{ $product->discount }} %</td>
                            <td>
                                <a class="button button-info" href="{{ route('manageProducts.edit', ['id' => $product->id]) }}">
                                    <i class="zmdi zmdi-edit"></i><span>Edit</span>
                                </a>
                                <br>
                                <button id="deleteTestimonial" class="button button-danger" data-toggle="modal" data-target="#exampleModalCenter" data-id="{{ $product->id }}" onclick="getProductId('{{ $product->id }}')">
                                    <i class="zmdi zmdi-delete"></i><span>Delete</span>
                                </button>
                                <br>
                                <a class="button button-info" href="{{ route('manageProductsOptions.create', ['product_id' => $product->id]) }}">
                                    <span>Add Option</span>
                                </a>
                                <br>
                                <a class="button button-info" href="{{ route('manageProductOptions.show', ['product_id' => $product->id]) }}">
                                    <span>Check Options</span>
                                </a>
                                <br>
                                <a class="button button-info" href="{{ route('manageProductReview.show', ['product_id' => $product->id]) }}">
                                    <span>Product Review</span>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    <!-- Modal -->
                    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
                         aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLongTitle">Modal title</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete this item?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close
                                    </button>
                                    <form id="deleteProductForm"
                                          action="{{route('manageProducts.destroy')}}" method="post">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-danger">Yes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </tbody>
            </table>

            <div class="d-flex justify-content-center">
                {!! $products->appends(request()->query())->links() !!}
            </div>
        </div>
    </div>
</div>
<!--Bordered Table Light End-->



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
<script src="{{asset("admin_assets/js/plugins/dropify/dropify.min.js")}}"></script>
<script src="{{asset("admin_assets/js/plugins/dropify/dropify.active.js")}}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    var product_id = 0;
    function getProductId(id)
    {
        product_id = id;
    }

    $(document).ready(function () {

        // Handle form submission for deletion
        $('#deleteProductForm').submit(function (e) {
            e.preventDefault(); // Prevent default form submission
            var formData = $(this).serialize(); // Serialize form data
            var url = $(this).attr('action'); // Get form action URL
            // Send AJAX request
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'DELETE',
                url: url,
                data: { id: product_id },
                // url: url,
                success: function (response) {
                    console.log('Product deleted successfully.');
                    // $('#exampleModalCenter').modal('hide');
                    location.reload();
                },
                error: function (xhr, status, error) {
                    alert(JSON.stringify(error));
                    console.error('Error deleting Product:', error);
                    // Handle errors or display error message
                }
            });
        });
    });
</script>


</body>


</html>
