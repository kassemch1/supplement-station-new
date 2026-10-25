@extends('layouts.admin')

@section('title', 'Manage Category')

@section('content')
        <!-- Page Headings Start -->
        <div class="row justify-content-between align-items-center mb-10">

            <!-- Page Heading Start -->
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Manage Categories</h3>
                </div>
            </div><!-- Page Heading End -->

        </div><!-- Page Headings End -->

        <!-- Add or Edit Product Start -->

        <div class="add-edit-product-wrap col-12">

            <div class="add-edit-product-form">
                <form id="categoryForm" method="post" action="{{route('manageCategories.update')}}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="category_id" value="{{$category->id}}">
                    <h4 class="title">Edit Category</h4>
                    <div class="row">
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Name</label>
                            <input name="name" class="form-control" type="text"  placeholder="Category Name" value="{{$category->name}}">
                        </div>
                    </div>
                    <!-- Success Alert -->
                    <div id="successAlert" class="alert alert-success mt-3" style="display: none;">
                        <strong>Success!</strong> Category updated successfully.
                    </div>
                    <!-- Error Alert -->
                    <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;">
                        <strong>Error!</strong> Failed to update Category. Please try again.
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
    $(document).ready(function() {
        $('#categoryForm').submit(function(e) {
            e.preventDefault(); // Prevent default form submission

            // Serialize form data
            var formData = $(this).serialize();

            // Send AJAX request
            $.ajax({
                type: 'PUT',
                url: $(this).attr('action'), // Use form's action attribute as URL
                data: formData,
                success: function(response) {
                    if(response.status ==='success'){
                        $('#successAlert').text(response.message).fadeIn();
                        setTimeout(function() {
                            $('#successAlert').fadeOut();
                        }, 3000);
                    }
                    else if(response.status==='exists'){
                        $('#errorAlert').text(response.message).fadeIn();
                        setTimeout(function() {
                            $('#errorAlert').fadeOut();
                        }, 3000);
                    }
                },
                error: function(xhr, status, error) {
                    $('#errorAlert').fadeIn();
                    setTimeout(function() {
                        $('#errorAlert').fadeOut();
                    }, 3000);
                }
            });
        });
    });
</script>
@endpush
