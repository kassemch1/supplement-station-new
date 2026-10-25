@extends('layouts.admin')

@section('title', 'Manage Plans')

@section('content')
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
                <form id="companyInfoForm" method="post" action="{{route('managePlan.update')}}"
                      enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <h4 class="title">Edit Plan</h4>
                    <input type="hidden" name="planId" value="{{$plane->id}}">
                    <div class="row">
                        <div class="col-lg-6 col-12 mb-30">
                            <label for="">Type</label>
                            <input name="type" class="form-control" type="text" placeholder="Type" value="{{$plane->type}}">
                        </div>
                        <div class="row">
                            <div class="col-lg-6 col-12 mb-30">
                                <label for="">Price</label>
                                <input name="price" class="form-control" type="text" placeholder="Price" value="{{$plane->price}}">
                            </div>
                        </div>

                        <div class="col-12 mb-30">
                            <label for="description">Point 1</label>
                            <textarea name="point1" class="form-control">{{$plane->point1}}</textarea>
                        </div>

                        <div class="col-12 mb-30">
                            <label for="description">Point 2</label>
                            <textarea name="point2" class="form-control">{{$plane->point2}}</textarea>
                        </div>

                        <div class="col-12 mb-30">
                            <label for="description">Point 3</label>
                            <textarea name="point3" class="form-control">{{$plane->point3}}</textarea>
                        </div>

                        <div class="col-12 mb-30">
                            <label for="description">Point 4</label>
                            <textarea name="point4" class="form-control">{{$plane->point4}}</textarea>
                        </div>

                        <div class="col-12 mb-30">
                            <label for="description">Point 5</label>
                            <textarea name="point5" class="form-control">{{$plane->point5}}</textarea>
                        </div>

                    </div>
                    <!-- Success Alert -->
                    <div id="successAlert" class="alert alert-success mt-3" style="display: none;">
                        <strong>Success!</strong> Plan edited successfully.
                    </div>
                    <!-- Error Alert -->
                    <div id="errorAlert" class="alert alert-danger mt-3" style="display: none;">
                        <strong>Error!</strong> Failed to Edit Plan. Please try again.
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
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('#companyInfoForm').submit(function (e) {
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
