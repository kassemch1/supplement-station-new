@extends('layouts.admin')

@section('title', 'Manage Products Options')

@section('content')
        <!-- Page Headings Start -->
        <div class="row justify-content-between align-items-center mb-10">

            <!-- Page Heading Start -->
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Manage Product Options</h3>
                </div>
            </div><!-- Page Heading End -->

        </div><!-- Page Headings End -->

 <!-- admin_views/manage_product_option/index.blade.php -->

<div class="col-lg-12 col-12 mb-30">
    <div class="box">
        <div class="box-head">
            <h4 class="title">Product Options</h4>
        </div>
        <div class="box-body">
            <table class="table table-bordered" data-ajax-list="list">
                <thead>
                <tr>
                    <th>Option Name</th>
                    <th>Option Value</th>
                    <th>Stock</th>
                    <th>Action</th>
                </tr>
                </thead>
                <tbody>
                @foreach($productOptions as $productOption)
                    <tr>
                        <td>{{ $productOption->option->option_name }}</td>
                        <td>{{ $productOption->option_value }}</td>
                        <td>{{ $productOption->stock ? 'In Stock' : 'Out of Stock' }}</td>
                        <td>
                            <a class="button button-info"
                               href="{{ route('manageProductsOptions.edit', ['id' => $productOption->id]) }}"><span>Edit</span></a>
                            <button class="button button-danger" data-toggle="modal"
                                    data-target="#deleteModal" data-id="{{ $productOption->id }}"
                                    onclick="getProductOptionId('{{ $productOption->id }}')"><span>Delete</span>
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <!-- Delete Modal -->
            <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog"
                 aria-labelledby="deleteModalTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="deleteModalLongTitle">Delete Confirmation</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete this product option?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <form id="deleteProductOptionForm" method="post">
                                @csrf
                                @method('delete')
                                <button type="submit" class="btn btn-danger">Yes, Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
                type: 'DELETE',
                url: url,
                data: { id: productOptionId }, // Use the correct ID variable
                success: function (response) {
                    AdminUI.toast(response.message || (typeof response.success === 'string' ? response.success : 'Deleted.'));
                    AdminUI.refresh();
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
                        AdminUI.toast(errorMsg, 'error');
                    } else {
                        AdminUI.toast('Error deleting product option. Please try again.', 'error');
                    }
                    console.error('Error deleting product option:', xhr);
                }
            });
        });
    });
</script>
@endpush
