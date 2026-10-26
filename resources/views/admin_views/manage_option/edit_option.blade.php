@extends('layouts.admin')

@section('title', 'Manage Options')

@section('content')
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
            <table class="table table-bordered" data-ajax-list="list">
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
@endsection

@push('scripts')
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
            type: 'DELETE',
            url: $(this).attr('action'), // Use the form action URL
            data: { option_id: option_id }, // Send the correct option_id
            success: function (response) {
                AdminUI.toast(response.message || (typeof response.success === 'string' ? response.success : 'Deleted.'));
                AdminUI.refresh();
            },
            error: function (xhr, status, error) {
                AdminUI.toast(xhr.responseJSON?.message || 'Could not delete. Please try again.', 'error');
                console.error('Error deleting Option:', error);
            }
        });
    });
});

</script>
@endpush
