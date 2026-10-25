@extends('layouts.admin')

@section('title', 'Manage Banner')

@section('content')
        <!-- Page Headings Start -->
        <div class="row justify-content-between align-items-center mb-10">

            <!-- Page Heading Start -->
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Manage Banner</h3>
                </div>
            </div><!-- Page Heading End -->

        </div><!-- Page Headings End -->

        <!--Bordered Table Light Start-->
        <div class="col-lg-6 col-12 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Banner</h4>
                </div>
                <div class="box-body">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th>Image</th>
                            <th>Product Name</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($banners as $banner)
                            <tr>
                                <td><img src="{{asset("$banner->image")}}" alt="Banner Image" width="120" height="100"></td>
                                <td>{{$banner->getProduct->name}}</td>

                                <td>
                                    <a class="button button-info"
                                       href="{{route("manageBanner.create")}}"><span>Update</span></a>
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
                                </div>
                            </div>
                        </div>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <!--Bordered Table Light End-->
@endsection

@push('scripts')
<script>
    var banner_id = 0;
    function getBannerId(id)
    {
        banner_id = id;
    }

    $(document).ready(function () {

        // Handle form submission for deletion
        $('#deleteProductForm').submit(function (e) {
            e.preventDefault(); // Prevent default form submission
            var formData = $(this).serialize(); // Serialize form data
            var url = $(this).attr('action'); // Get form action URL
            // Send AJAX request
            $.ajax({
                type: 'DELETE',
                url: url,
                data: { id: banner_id },
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
@endpush
