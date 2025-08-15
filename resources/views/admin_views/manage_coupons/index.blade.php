<!doctype html>
<html class="no-js" lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Admin | Manage Coupons</title>
    <meta name="robots" content="noindex, follow" />
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/material-design-iconic-font.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/themify-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/vendor/cryptocurrency-icons.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/plugins/plugins.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/helper.css') }}">
    <link rel="stylesheet" href="{{ asset('admin_assets/css/style.css') }}">
    <link id="cus-style" rel="stylesheet" href="{{ asset('admin_assets/css/style-primary.css') }}">
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
                    <h3>Manage Coupons</h3>

                    <!-- Search and Filter Form -->
                    <div class="mt-3">
                        <form method="GET" action="{{ route('admin.coupons.index') }}" class="form-inline">
                            <input type="text" name="search" class="form-control mr-2" placeholder="Search by code or name" value="{{ request('search') }}">
                            
                            <select name="type" class="form-control mr-2">
                                <option value="">All Types</option>
                                <option value="reusable" {{ request('type') == 'reusable' ? 'selected' : '' }}>Reusable</option>
                                <option value="single_use" {{ request('type') == 'single_use' ? 'selected' : '' }}>Single Use</option>
                            </select>
                            
                            <select name="status" class="form-control mr-2">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            
                            <button type="submit" class="btn btn-primary mr-2">Search</button>
                            <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Clear</a>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-12 col-lg-auto mb-20">
                <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
                    <i class="zmdi zmdi-plus"></i> Add New Coupon
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                @if($coupons->count() > 0)
                <div class="table-responsive">
                    <table class="table table-vertical-middle">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Discount</th>
                                <th>Min. Order</th>
                                <th>Used</th>
                                <th>Status</th>
                                <th>Expires</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($coupons as $coupon)
                            <tr>
                                <td>
                                    <strong>{{ $coupon->code }}</strong>
                                    @if($coupon->description)
                                        <br><small class="text-muted">{{ Str::limit($coupon->description, 30) }}</small>
                                    @endif
                                </td>
                                <td>{{ $coupon->name }}</td>
                                <td>
                                    <span class="badge badge-{{ $coupon->type == 'reusable' ? 'info' : 'warning' }}">
                                        {{ ucfirst(str_replace('_', ' ', $coupon->type)) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success">{{ $coupon->formatted_discount }}</span>
                                </td>
                                <td>${{ number_format($coupon->minimum_order_amount, 2) }}</td>
                                <td>
                                    {{ $coupon->used_count }}
                                    @if($coupon->max_uses)
                                        / {{ $coupon->max_uses }}
                                    @else
                                        <span class="text-muted">/ ∞</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-{{ $coupon->status_text == 'Active' ? 'success' : 'danger' }}">
                                        {{ $coupon->status_text }}
                                    </span>
                                </td>
                                <td>
                                    @if($coupon->expires_at)
                                        {{ $coupon->expires_at->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="action h4">
                                    <div class="table-action-buttons">
                                        <a class="view button button-box button-xs button-primary" href="{{ route('admin.coupons.edit', $coupon->id) }}">
                                            <i class="zmdi zmdi-edit"></i>
                                        </a>
                                        
                                        <button class="button button-box button-xs button-{{ $coupon->is_active ? 'warning' : 'success' }}" 
                                                onclick="confirmToggleStatus('{{ route('admin.coupons.toggle-status', $coupon->id) }}', '{{ $coupon->is_active ? 'deactivate' : 'activate' }}')">
                                            <i class="zmdi zmdi-{{ $coupon->is_active ? 'close' : 'check' }}"></i>
                                        </button>
                                        
                                        <button class="button button-box button-xs button-danger" 
                                                onclick="confirmDeleteCoupon('{{ route('admin.coupons.destroy', $coupon->id) }}')">
                                            <i class="zmdi zmdi-delete"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    
                    <div class="d-flex justify-content-center">
                        {{ $coupons->appends(request()->query())->links() }}
                    </div>
                </div>
                @else
                <div class="text-center text-muted py-5">
                    <i class="zmdi zmdi-tag zmdi-hc-3x mb-3"></i>
                    <h5>No coupons found</h5>
                    <p>Create your first coupon to get started.</p>
                    <a href="{{ route('admin.coupons.create') }}" class="btn btn-primary">
                        <i class="zmdi zmdi-plus"></i> Add New Coupon
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Toggle Status Confirmation Modal -->
    <div class="modal fade" id="toggleStatusModal" tabindex="-1" role="dialog" aria-labelledby="toggleStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="toggleStatusModalLabel">Toggle Coupon Status</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to <span id="actionType"></span> this coupon?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <form id="toggleStatusForm" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success">Yes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmationModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmationModalLabel">Delete Coupon</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this coupon? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <form id="deleteCouponForm" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div><!-- Main Wrapper End -->

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
<script src="{{ asset('admin_assets/js/vendor/modernizr-3.6.0.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/jquery-3.3.1.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/popper.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/vendor/bootstrap.min.js') }}"></script>
<script src="{{ asset('admin_assets/js/plugins/plugins.js') }}"></script>
<script src="{{ asset('admin_assets/js/main.js') }}"></script>

<script>
    function confirmToggleStatus(actionUrl, actionType) {
        document.getElementById('toggleStatusForm').action = actionUrl;
        document.getElementById('actionType').textContent = actionType;
        $('#toggleStatusModal').modal('show');
    }

    function confirmDeleteCoupon(actionUrl) {
        document.getElementById('deleteCouponForm').action = actionUrl;
        $('#deleteConfirmationModal').modal('show');
    }
</script>

</body>
</html>