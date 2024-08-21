<!doctype html>
<html class="no-js" lang="en">



<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Manage Options</title>
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
                    <h3>Manage Options</h3>
                </div>
            </div><!-- Page Heading End -->

        </div><!-- Page Headings End -->

       <!-- Bordered Table Light Start -->
<div class="col-lg-6 col-12 mb-30">
    <div class="box">
        <div class="box-head">
            <h4 class="title">Options</h4>
        </div>
        <div class="box-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Option Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($options as $option)
                        <tr>
                            <td>{{ $option->option_name }}</td>
                            <td>
                                <a class="button button-info"
                                   href="{{ route('manageOptions.edit', ['id' => $option->id]) }}">
                                    <span>Edit</span>
                                </a>
                                <button id="deleteOption" class="button button-danger" 
                                        data-toggle="modal" 
                                        data-target="#exampleModalCenter" 
                                        data-id="{{ $option->id }}" 
                                        onclick="getOptionId('{{ $option->id }}')">
                                    <span>Delete</span>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                    <!-- Modal -->
                    <div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog"
                         aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLongTitle">Confirm Deletion</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    Are you sure you want to delete this option?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <form id="deleteOptionForm" action="{{ route('manageOptions.destroy') }}" method="post">
                                        @csrf
                                        @method('delete')
                                        <input type="hidden" name="option_id" value="">
                                        <button type="submit" class="btn btn-danger">Yes</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Bordered Table Light End -->





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
 var option_id = 0;
function getOptionId(id) {
    console.log(id);
    option_id = id;
    $('input[name="option_id"]').val(option_id); // Set hidden input value
}

$(document).ready(function () {
    // Handle form submission for deletion
    $('#deleteOptionForm').submit(function (e) {
        e.preventDefault(); // Prevent default form submission

        // Send AJAX request
        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            type: 'DELETE',
            url: $(this).attr('action'), // Use the form action URL
            data: { option_id: option_id }, // Send the correct option_id
            success: function (response) {
                console.log('Option deleted successfully.');
                location.reload(); // Reload the page on success
            },
            error: function (xhr, status, error) {
                alert(JSON.stringify(error));
                console.error('Error deleting Option:', error);
            }
        });
    });
});

</script>


</body>



</html>
