@extends('layouts.admin')

@section('title', 'Manage Products')

@section('content')
        <!-- Page Headings Start -->
        <div class="row justify-content-between align-items-center mb-10">

            <!-- Page Heading Start -->
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Manage Products</h3>
                </div>
            </div><!-- Page Heading End -->

        </div><!-- Page Headings End -->

<!-- Combined Filter Form -->
<form method="GET" action="{{ route('manageProducts.index') }}" class="mb-3" data-ajax-filter>
    <!-- Search Bar -->
    <input type="text" name="search" placeholder="Search for products..." value="{{ request('search') }}" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px;"/>

    <!-- Dropdown for Categories -->
    <select name="category_id" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; margin-right: 10px;">
        <option value="">Select Category</option>
        @foreach($categories as $category)
            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
        @endforeach
    </select>

    <!-- Dropdown for Stock Status -->
    <select name="stock" style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; margin-right: 10px;">
        <option value="">Select Stock Status</option>
        <option value="in-stock" {{ request('stock') == 'in-stock' ? 'selected' : '' }}>In Stock</option>
        <option value="out-of-stock" {{ request('stock') == 'out-of-stock' ? 'selected' : '' }}>Out of Stock</option>
        <option value="archived" {{ request('stock') == 'archived' ? 'selected' : '' }}>Deleted (archived)</option>
    </select>

    <button type="submit" style="padding: 5px 10px; border: none; background-color: #17A2B8; color: white; border-radius: 4px; cursor: pointer;">Filter</button>
</form>

<div class="col-lg-6 col-12 mb-30">
    <div class="box">
        <div class="box-head">
            <h4 class="title">Products</h4>
        </div>
        <div class="box-body">

            <!-- Products Table -->
            <table class="table table-bordered" data-ajax-list="products-table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Stock</th>
                        <th>Discount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="productTableBody">
                    @foreach($products as $product)
                        <tr>
                            <td>
                                @if($product->images->isNotEmpty())
                                    <img src="{{ asset($product->images->first()->url) }}" alt="img">
                                @else
                                    No image available
                                @endif
                            </td>
                            <td style="white-space: pre-wrap; word-wrap: break-word;">{{ $product->name }}</td>
                            <td>{{ $product->price }} $</td>
                            <td style="max-width: 250px" class="text-truncate">{{ $product->description }}</td>
                            <td>{{ $product->getCategory->name }}</td>
                            <td>
                                @if($product->trashed())
                                    <span class="badge badge-secondary">Deleted</span>
                                @elseif((int)$product->stock === 1)
                                    <span class="badge badge-success">In Stock</span>
                                @else
                                    <span class="badge badge-danger">Out of Stock</span>
                                @endif
                            </td>
                            <td>{{ $product->discount }} %</td>
                            <td>
                                @if($product->trashed())
                                <form method="POST" action="{{ route('manageProducts.restore', ['id' => $product->id]) }}" data-ajax-form data-success="Product restored to the shop." onsubmit="return confirm('Restore this product to the shop?')">
                                    @csrf
                                    <button type="submit" class="button button-success"><i class="zmdi zmdi-undo"></i><span>Restore</span></button>
                                </form>
                                @else
                                <a class="button button-info" href="{{ route('manageProducts.edit', ['id' => $product->id]) }}">
                                    <i class="zmdi zmdi-edit"></i><span>Edit</span>
                                </a>
                                <br>
                                <button id="deleteTestimonial" class="button button-danger" data-toggle="modal" data-target="#exampleModalCenter" data-id="{{ $product->id }}" onclick="getProductId('{{ $product->id }}')">
                                    <i class="zmdi zmdi-delete"></i><span>Delete</span>
                                </button>
                                <br>
                                <button class="button button-success" onclick="sendProductEmail({{ $product->id }})" title="Send to subscribers">
                                    <i class="zmdi zmdi-email"></i><span>Send Email</span>
                                </button>
                                <br>
                                <a class="button button-info" href="{{ route('manageProductsOptions.create', ['product_id' => $product->id]) }}">
                                    <span>Add Option</span>
                                </a>
                                <br>
                                <a class="button button-info" href="{{ route('manageProductOptions.show', ['product_id' => $product->id]) }}">
                                    <span>Check Options</span>
                                </a>
                                <br>
                                <a class="button button-info" href="{{ route('manageProductReview.show', ['product_id' => $product->id]) }}">
                                    <span>Product Review</span>
                                </a>
                                @endif
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
                                    <form id="deleteProductForm"
                                          action="{{route('manageProducts.destroy')}}" method="post">
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

            <div class="d-flex justify-content-center" data-ajax-list="products-pages">
                {!! $products->appends(request()->query())->links() !!}
            </div>
        </div>
    </div>
</div>
<!--Bordered Table Light End-->
@endsection

@push('scripts')
<script>

    var product_id = 0;
    function getProductId(id)
    {
        product_id = id;
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
                data: { id: product_id },
                // url: url,
                success: function (response) {
                    AdminUI.toast('Product deleted.');
                    AdminUI.refresh();
                },
                error: function (xhr, status, error) {
                    AdminUI.toast(xhr.responseJSON?.message || 'Could not delete. Please try again.', 'error');
                    console.error('Error deleting Product:', error);
                    // Handle errors or display error message
                }
            });
        });
    });

    // Add this JavaScript to your existing script section

    function sendProductEmail(productId) {
        if (confirm('Are you sure you want to send this product to all active subscribers?')) {
            // Disable the button to prevent multiple clicks
            const button = event.target.closest('button');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="zmdi zmdi-hourglass-alt zmdi-hc-spin"></i><span>Sending...</span>';
            button.disabled = true;

            $.ajax({
                type: 'POST',
                url: '/admin/products/send-email',
                data: {
                    product_id: productId
                },
                success: function (response) {
                    if (response.success) {
                        AdminUI.toast(response.message);
                    } else {
                        AdminUI.toast(response.message, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    let errorMessage = 'Failed to send emails. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }
                    AdminUI.toast(errorMessage, 'error');
                    console.error('Error sending product email:', error);
                },
                complete: function() {
                    // Re-enable the button
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            });
        }
    }
</script>
@endpush
