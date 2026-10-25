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

        <!--Bordered Table Light Start-->
        <div class="col-lg-6 col-12 mb-30">
            <div class="box">
                <div class="box-head">
                    <h4 class="title">Plans</h4>
                </div>
                <div class="box-body">
                    <table class="table table-bordered" data-ajax-list="list">
                        <thead>
                        <tr>
                            <th>Type</th>
                            <th>Price</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($planes as $plane)
                            <tr>
                                <td>{{$plane->type}}</td>
                                <td>{{$plane->price}}</td>
                                <td>
                                    <a class="button button-info"
                                       href="{{route("managePlan.edit",['id'=>$plane->id])}}"><span>Edit</span></a>
                                    <button id="deleteTestimonial" class="button button-danger" data-toggle="modal"
                                            data-target="#exampleModalCenter" data-id="{{$plane->id}}" onclick="getPlanId('{{$plane->id}}')"><span>Delete</span>
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
                                        <form id="deleteProjectForm"
                                              action="{{route('managePlan.destroy')}}" method="post">
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
                </div>
            </div>
        </div>
        <!--Bordered Table Light End-->
@endsection

@push('scripts')
<script>
    var plan_id = 0;
    function getPlanId(id)
    {
        plan_id = id;
    }

    $(document).ready(function () {

        // Handle form submission for deletion
        $('#deleteProjectForm').submit(function (e) {
            e.preventDefault(); // Prevent default form submission
            var formData = $(this).serialize(); // Serialize form data
            var url = $(this).attr('action'); // Get form action URL
            // Send AJAX request
            $.ajax({
                type: 'DELETE',
                url: url,
                data: { id: plan_id },
                // url: url,
                success: function (response) {
                    AdminUI.toast(response.message || (typeof response.success === 'string' ? response.success : 'Deleted.'));
                    AdminUI.refresh();
                },
                error: function (xhr, status, error) {
                    AdminUI.toast(xhr.responseJSON?.message || 'Could not delete. Please try again.', 'error');
                    console.error('Error deleting Plane:', error);
                    // Handle errors or display error message
                }
            });
        });
    });
</script>
@endpush
