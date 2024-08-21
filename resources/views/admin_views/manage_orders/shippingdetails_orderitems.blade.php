<!doctype html>
<html class="no-js" lang="en">



<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Manage Orders</title>
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

    <!-- Order Details Content -->
<div class="content-body">
    <div class="row justify-content-between align-items-center mb-10">
        <div class="col-12 col-lg-auto mb-20">
            <div class="page-heading">
                <h3>Order Details<h3>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <h4>Order ID: #{{ $order->id }}</h4>
            <h5>Total Amount: ${{ $order->total_amount }}</h5>
            <h5>Status: {{ $order->status }}</h5>
            <h5>Date: {{ $order->created_at }}</h5>
            <hr>
            <h4>Order Items:</h4>
            <table class="table table-vertical-middle">
                <thead>
                    <tr>
                        <th>Product Image</th>
                        <th>Product Name</th>
                        <th>Product ID</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Options</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orderItems as $item)
                    <tr>
                        <td>
                            @if ($item->product->images->isNotEmpty())
                            <img src="{{ asset($item->product->images->first()->url) }}" alt="Product Image" style="width: 50px; height: 50px;">
                            @else
                            <span>No Image</span>
                            @endif
                        </td>
                        <td>{{ $item->product->name }}</td>
                        <td>{{ $item->product_id }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>${{ $item->price }}</td>
                        <td>
                            @foreach (json_decode($item->selected_options, true) as $key => $value)
                            <div>{{ ucfirst($key) }}: {{ ucfirst($value) }}</div>
                            @endforeach
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <hr>
            <h4>Shipping Details:</h4>
            <p>Name: {{ $shippingDetail->name }}</p>
            <p>Phone: {{ $shippingDetail->phone }}</p>
            <p>Address: {{ $shippingDetail->address }}</p>
            <p>City: {{ $shippingDetail->city }}</p>
        </div>
    </div>
</div>


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
    var productOptionId = 0;

    function getProductOptionId(id) {
        productOptionId = id;
        var actionUrl = "{{ route('manageProductOptions.destroy', ['id' => ':id']) }}";
        actionUrl = actionUrl.replace(':id', id);
        $('#deleteProductOptionForm').attr('action', actionUrl);
    }

    $(document).ready(function () {
        // Handle form submission for deletion
        $('#deleteProductOptionForm').submit(function (e) {
            e.preventDefault(); // Prevent default form submission
            var url = $(this).attr('action'); // Get form action URL
            // Send AJAX request
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'DELETE',
                url: url,
                data: { id: productOptionId }, // Use the correct ID variable
                success: function (response) {
                    console.log('Product option deleted successfully.');
                    location.reload(); // Reload the page after successful deletion
                },
                error: function (xhr) {
                    // Check if there are validation errors
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMsg = '';
                        for (let key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                errorMsg += errors[key][0] + '<br>'; // Concatenate error messages
                            }
                        }
                        alert(errorMsg); // Show validation errors
                    } else {
                        alert('Error deleting product option. Please try again.'); // General error message
                    }
                    console.error('Error deleting product option:', xhr);
                }
            });
        });
    });
</script>

</body>



</html>
