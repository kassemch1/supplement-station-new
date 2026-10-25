@extends('layouts.admin')

@section('title', 'Product Reviews')

@push('styles')
<style>

    .modal-dialog {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
    }
    </style>
@endpush

@section('content')
        <div class="row justify-content-between align-items-center mb-10">
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Product Reviews</h3>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h4>Product ID: #{{ $product_id }}</h4>
                <hr>
                <h4>Reviews:</h4>
                <table class="table table-vertical-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Message</th>
                            <th>Rating</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reviews as $review)
                        <tr>
                            <td>{{ $review->name }}</td>
                            <td>{{ $review->message }}</td>
                            <td>{{ $review->rating }}/5</td>
                            <td>{{ $review->created_at->format('Y-m-d') }}</td>
                            <td>
                                <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal" onclick="setReviewId({{ $review->id }})">Delete</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this review?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <form id="deleteReviewForm" action="" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="button" id="confirmDelete" class="btn btn-danger">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    var reviewId = 0;

    function setReviewId(id) {
        reviewId = id;
        var actionUrl = "{{ route('manageProductReview.destroyReview', ['id' => ':id']) }}";
        actionUrl = actionUrl.replace(':id', reviewId);
        $('#deleteReviewForm').attr('action', actionUrl);
    }

    $('#confirmDelete').on('click', function () {
        var form = $('#deleteReviewForm');
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: form.serialize(),
            success: function (response) {
                // Refresh the page on successful deletion
                location.reload();
            },
            error: function (xhr, status, error) {
                // Handle errors here
                alert('An error occurred. Please try again.');
            }
        });
    });
  </script>
@endpush
