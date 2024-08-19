<!doctype html>
<html class="no-js" lang="en">



<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Manage Orders</title>
    <meta name="robots" content="noindex, follow" />
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
    @include('partials/adminHeader')
    <!-- Header Section End -->

    <!-- Side Header Start -->
    @include('partials/adminSideBar')
    <!-- Side Header End -->

    <div class="content-body">
        <div class="row justify-content-between align-items-center mb-10">
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Delivered Orders</h3>

                    <div>
                        <form action="{{ route('admin.orders.delivered') }}" method="GET" class="form-inline">
                            <label for="sort" class="mr-2">Sort By:</label>
                            <select name="sort" id="sort" class="form-control mr-2" onchange="this.form.submit()">
                                <option value="desc" {{ request('sort') == 'desc' ? 'selected' : '' }}>Newest First</option>
                                <option value="asc" {{ request('sort') == 'asc' ? 'selected' : '' }}>Oldest First</option>
                            </select>
                        </form>
                    </div>
                   
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <div class="table-responsive">
                    <table class="table table-vertical-middle">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($deliveredOrders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>${{ $order->total_amount }}</td>
                                <td><span class="badge badge-success">{{ $order->status }}</span></td>
                                <td>{{ $order->created_at }}</td>
                                <td class="action h4">
                                    <div class="table-action-buttons">
                                        <a class="view button button-box button-xs button-primary" href="{{ route('admin.orders.details', $order->id) }}"><i class="zmdi zmdi-more"></i></a>
                                        <button class="button button-box button-xs button-warning" onclick="confirmUpdateStatus('{{ route('admin.orders.updateStatus', $order->id) }}')"><i class="zmdi zmdi-refresh"></i></button>
                                        <button class="button button-box button-xs button-danger" onclick="confirmDeleteOrder('{{ route('admin.orders.delete', $order->id) }}')"><i class="zmdi zmdi-delete"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center"> <!-- Center the pagination -->
                        {{ $deliveredOrders->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

   <!-- Delete Confirmation Modal -->
   <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Delete Order</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this order?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <form id="deleteOrderForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Update Status Confirmation Modal -->
<div class="modal fade" id="updateStatusConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="updateStatusConfirmationModalLabel" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="updateStatusConfirmationModalLabel">Update Order Status</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            Are you sure you want to update the status of this order?
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            <form id="updateStatusForm" method="POST">
                @csrf
                <input type="hidden" name="status" value="pending"> <!-- or the appropriate status value -->
                <button type="submit" class="btn btn-success">Yes</button>
            </form>
        </div>
    </div>
</div>
</div>


<!-- JS -->
<!-- Load jQuery first -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Load Popper.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>

<!-- Load Bootstrap JS -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>

<!-- Other JS files -->
<script src={{asset("admin_assets/js/vendor/modernizr-3.6.0.min.js")}}></script>
<script src={{asset("admin_assets/js/vendor/jquery-3.3.1.min.js")}}></script>
<script src={{asset("admin_assets/js/vendor/popper.min.js")}}></script>
<script src={{asset("admin_assets/js/vendor/bootstrap.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/perfect-scrollbar.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/tippy4.min.js.js")}}></script>
<script src={{asset("admin_assets/js/main.js")}}></script>
<script src={{asset("admin_assets/js/plugins/nice-select/jquery.nice-select.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/nice-select/niceSelect.active.js")}}></script>
<script src={{asset("admin_assets/js/plugins/filepond/filepond.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/filepond/filepond-plugin-image-exif-orientation.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/filepond/filepond-plugin-image-preview.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/filepond/filepond.active.js")}}></script>

<script>
function confirmDeleteOrder(actionUrl) {
    document.getElementById('deleteOrderForm').action = actionUrl;
    $('#deleteConfirmationModal').modal('show');
}

function confirmUpdateStatus(actionUrl) {
    document.getElementById('updateStatusForm').action = actionUrl;
    $('#updateStatusConfirmationModal').modal('show');
}
</script>
</body>



</html>
