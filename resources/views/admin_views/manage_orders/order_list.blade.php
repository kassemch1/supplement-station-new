@extends('layouts.admin')

@section('title', 'Manage Orders')

@section('content')
        <div class="row justify-content-between align-items-center mb-10">
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>{{ $heading }}</h3>
                    <div>
                        <form action="{{ route('admin.orders.' . $status) }}" method="GET" class="form-inline" data-ajax-filter>
                            <label for="sort" class="mr-2">Sort By:</label>
                            <select name="sort" id="sort" class="form-control mr-2" data-clear-to="desc">
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
                <div class="table-responsive" data-ajax-list="orders">
                    <table class="table table-vertical-middle">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Coupon Code</th>
                                <th>Coupon Discount</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($orders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td>${{ number_format($order->total_amount, 2) }}</td>
                                <td><span class="badge badge-{{ ['pending' => 'danger', 'delivered' => 'success'][$order->status] ?? 'secondary' }}">{{ ucfirst($order->status) }}</span></td>
                                <td>
                                    @if($order->coupon_code)
                                        <span class="badge badge-info">{{ $order->coupon_code }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($order->coupon_discount_amount)
                                        <span class="badge badge-success">-${{ number_format($order->coupon_discount_amount, 2) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>{{ $order->created_at->timezone(config('app.display_timezone'))->format('M d, Y - H:i') }}</td>
                                <td class="action h4">
                                    <div class="table-action-buttons">
                                        <a class="view button button-box button-xs button-primary" href="{{ route('admin.orders.details', $order->id) }}"><i class="zmdi zmdi-more"></i></a>
                                        @if($status === 'pending')
                                        <button class="button button-box button-xs button-success" onclick="confirmUpdateStatus('{{ route('admin.orders.updateStatus', $order->id) }}')"><i class="zmdi zmdi-check"></i></button>
                                        @else
                                        <button class="button button-box button-xs button-warning" onclick="confirmUpdateStatus('{{ route('admin.orders.updateStatus', $order->id) }}')"><i class="zmdi zmdi-refresh"></i></button>
                                        @endif
                                        <button class="button button-box button-xs button-danger" onclick="confirmDeleteOrder('{{ route('admin.orders.delete', $order->id) }}')"><i class="zmdi zmdi-delete"></i></button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center">
                        {{ $orders->links() }}
                    </div>
                </div>
            </div>
        </div>

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
                    <form id="deleteOrderForm" method="POST" data-ajax-form data-success="Order deleted.">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Yes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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
                    <form id="updateStatusForm" method="POST" data-ajax-form data-success="Order status updated.">
                        @csrf
                        <input type="hidden" name="status" value="{{ $status === 'pending' ? 'delivered' : 'pending' }}">
                        <button type="submit" class="btn btn-success">Yes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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
@endpush
