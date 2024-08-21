<!doctype html>
<html class="no-js" lang="en">


<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Manage Plans</title>
    <meta name="robots" content="noindex, follow"/>
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
                    <h3>Manage Plans</h3>
                </div>
            </div><!-- Page Heading End -->

        </div><!-- Page Headings End -->

        <!-- Add or Edit Product Start -->

        <div class="add-edit-product-wrap col-12">

            <div class="add-edit-product-form">
                <form id="companyInfoForm" method="post" action="{{route('managePlan.store')}}"
                      enctype="multipart/form-data">
                    @csrf
                    <h4 class="title">Add Plane</h4>

                    <div class="row">
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Type</label>
                            <input name="type" class="form-control" type="text" placeholder="Type" value="">
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-12 mb-30">
                                <label for="">Price</label>
                                <input name="price" class="form-control" type="text" placeholder="Price" value="">
                            </div>
                        </div>

                        <div class="col-12 mb-30">
                            <label for="description">Point 1</label>
                            <textarea name="point1" class="form-control"></textarea>
                        </div>

                        <div class="col-12 mb-30">
                            <label for="description">Point 2</label>
                            <textarea name="point2" class="form-control"></textarea>
                        </div>

                        <div class="col-12 mb-30">
                            <label for="description">Point 3</label>
                            <textarea name="point3" class="form-control"></textarea>
                        </div>

                        <div class="col-12 mb-30">
                            <label for="description">Point 4</label>
                            <textarea name="point4" class="form-control"></textarea>
                        </div>

                        <div class="col-12 mb-30">
                            <label for="description">Point 5</label>
                            <textarea name="point5" class="form-control"></textarea>
                        </div>

                    </div>
                    <!-- Success Alert -->
                    <div id="successAlert" class="alert alert-success mt-3" style="display: none;">
                        <strong>Success!</strong> Plan added successfully.
                    </div>
                    <!-- Error Alert -->
                    <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;">
                        <strong>Error!</strong> Failed to add Plan. Please try again.
                    </div>
                    <!-- Button Group Start -->
                    <div class="row">
                        <div class="d-flex flex-wrap justify-content-end col mbn-10">
                            <button class="button button-outline button-primary mb-10 ml-10 mr-0" type="submit"
                                    id="submitBtn">Save & Publish
                            </button>
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
<script src="{{asset("admin_assets/js/plugins/dropify/dropify.min.js")}}"></script>
<script src="{{asset("admin_assets/js/plugins/dropify/dropify.active.js")}}"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#companyInfoForm').submit(function (e) {
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
