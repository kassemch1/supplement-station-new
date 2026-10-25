@extends('layouts.admin')

@section('title', 'Manage Subscribers')

@push('styles')
<style>
        body.skin-dark {
            background-color: #202334;
            color: #F5F7FA;
        }

        /* Card Styling */
        .card {
            background: #2B2F42 !important;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            border: none;
        }

        .card-body {
            padding: 2.5rem;
        }

        /* Form Fields */
        .form-control,
        .form-select,
        .input-group-text {
            background: #fff;
            border-radius: 6px;
            border: 1px solid #3A7DFF22;
            color: #202334;
        }

        /* Labels */
        label {
            font-weight: 600;
            color: #F5F7FA;
        }

        /* Buttons */
        .btn-primary {
            background-color: #3A7DFF;
            border-color: #3A7DFF;
        }

        .btn-primary:hover {
            background-color: #2E6AE6;
            border-color: #2E6AE6;
        }

        .btn-secondary {
            background-color: #B0BEC5;
            border-color: #B0BEC5;
            color: #202334;
        }

        .btn-secondary:hover {
            background-color: #A0B0B8;
            border-color: #A0B0B8;
            color: #202334;
        }

        .btn-success {
            background-color: #4CAF50;
            border-color: #4CAF50;
        }

        .btn-warning {
            background-color: #FF9800;
            border-color: #FF9800;
        }

        .btn-danger {
            background-color: #F44336;
            border-color: #F44336;
        }

        /* Headings */
        h3 {
            color: #F5F7FA;
        }

        /* Table */
        .table {
            color: #F5F7FA;
        }

        .table-light {
            background-color: #3A3F52;
        }

        .table-hover tbody tr:hover {
            background-color: #333749;
        }

        /* Stats Cards */
        .stats-card {
            background: #2B2F42;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border: 1px solid #3A7DFF22;
        }

        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: #3A7DFF;
        }

        .stats-label {
            color: #B0BEC5;
        }

        /* Bulk Actions */
        .bulk-actions {
            background-color: #333749;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: none;
        }

        /* Badges */
        .badge {
            font-size: 0.8rem;
            padding: 0.25rem 0.5rem;
        }

        .bg-success {
            background-color: #4CAF50 !important;
        }

        .bg-secondary {
            background-color: #6c757d !important;
        }

        /* Alert */
        .alert-success {
            background-color: #4CAF50;
            border-color: #4CAF50;
            color: white;
        }

        .text-muted {
            color: #B0BEC5 !important;
        }

        /* Checkbox Styling */
        .form-check-input {
            width: 18px;
            height: 18px;
            background-color: #fff;
            border: 2px solid #dee2e6;
            border-radius: 0.25rem;
            margin-top: 0;
            margin-left: 0;
            vertical-align: top;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            transition: all 0.15s ease-in-out;
        }

        .form-check-input:checked {
            background-color: #3A7DFF;
            border-color: #3A7DFF;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 20 20'%3e%3cpath fill='none' stroke='%23fff' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='M6 10l3 3l6-6'/%3e%3c/svg%3e");
            background-size: 100% 100%;
            background-position: center;
            background-repeat: no-repeat;
        }

        .form-check-input:focus {
            border-color: #3A7DFF;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(58, 125, 255, 0.25);
        }

        /* Table checkbox alignment */
        .table td .form-check-input {
            margin: 0;
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
        <!-- Page Headings Start -->
        <div class="row justify-content-between align-items-center mb-10">
            <!-- Page Heading Start -->
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Subscriber Management</h3>
                </div>
            </div><!-- Page Heading End -->

            <!-- Page Button Start -->
            <div class="col-12 col-lg-auto mb-20">
                <a href="{{ route('admin.subscribers.create') }}" class="btn btn-primary">
                    <i class="zmdi zmdi-plus"></i> Add Subscriber
                </a>
                <button type="button" class="btn btn-success" onclick="exportSubscribers()">
                    <i class="zmdi zmdi-download"></i> Export CSV
                </button>
            </div><!-- Page Button End -->
        </div><!-- Page Headings End -->

        <!-- Stats Cards -->
        <div class="row mb-30" data-ajax-list="subscriber-stats">
            <div class="col-lg-3 col-md-6 col-12 mb-20">
                <div class="stats-card">
                    <div class="stats-number">{{ $stats['total'] }}</div>
                    <div class="stats-label">Total Subscribers</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12 mb-20">
                <div class="stats-card">
                    <div class="stats-number">{{ $stats['active'] }}</div>
                    <div class="stats-label">Active Subscribers</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12 mb-20">
                <div class="stats-card">
                    <div class="stats-number">{{ $stats['inactive'] }}</div>
                    <div class="stats-label">Inactive Subscribers</div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-12 mb-20">
                <div class="stats-card">
                    <div class="stats-number">{{ $stats['recent'] }}</div>
                    <div class="stats-label">New This Month</div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
            </div>
        @endif

        <!-- Subscribers List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <!-- Search Form -->
                        <form method="GET" action="{{ route('admin.subscribers.index') }}" class="mb-30" data-ajax-filter>
                            <div class="row">
                                <div class="col-lg-4 col-12 mb-20">
                                    <input type="text" name="search" class="form-control" placeholder="Search by email..."
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-lg-3 col-12 mb-20">
                                    <select name="status" class="form-control">
                                        <option value="">All Status</option>
                                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                    </select>
                                </div>
                                <div class="col-lg-3 col-12 mb-20 d-flex" style="gap: 8px" data-clear-slot>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="zmdi zmdi-search"></i> Search
                                    </button>
                                </div>
                                <div class="col-lg-2 col-12 mb-20">
                                    <button type="button" class="btn btn-secondary" onclick="toggleBulkActions()">
                                        <i class="zmdi zmdi-check-all"></i> Bulk Actions
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Bulk Actions -->
                        <div class="bulk-actions" id="bulkActions">
                            <div class="row align-items-center">
                                <div class="col-lg-8 col-12">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success btn-sm" onclick="bulkAction('activate')">
                                            <i class="zmdi zmdi-check"></i> Activate Selected
                                        </button>
                                        <button type="button" class="btn btn-warning btn-sm" onclick="bulkAction('deactivate')">
                                            <i class="zmdi zmdi-pause"></i> Deactivate Selected
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="bulkAction('delete')">
                                            <i class="zmdi zmdi-delete"></i> Delete Selected
                                        </button>
                                    </div>
                                </div>
                                <div class="col-lg-4 col-12 text-right">
                                    <span id="selectedCount">0 selected</span>
                                </div>
                            </div>
                        </div>

                        <!-- Subscribers Table -->
                        <div data-ajax-list="subscribers">
                        @if($subscribers->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead class="table-light">
                                    <tr>
                                        <th width="50">
                                            <input type="checkbox" id="selectAll" class="form-check-input">
                                        </th>
                                        <th>Email</th>
                                        <th>Status</th>
                                        <th>Subscribed Date</th>
                                        <th>Last Updated</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($subscribers as $subscriber)
                                        <tr>
                                            <td>
                                                <input type="checkbox" class="form-check-input subscriber-checkbox"
                                                       value="{{ $subscriber->id }}">
                                            </td>
                                            <td>
                                                <strong>{{ $subscriber->email }}</strong>
                                            </td>
                                            <td>
                                                    <span class="badge {{ $subscriber->is_active ? 'bg-success' : 'bg-secondary' }}" style="color: white">
                                                        {{ $subscriber->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                            </td>
                                            <td>{{ $subscriber->subscribed_at->format('M d, Y H:i') }}</td>
                                            <td>{{ $subscriber->updated_at->format('M d, Y H:i') }}</td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-primary btn-sm"
                                                            onclick="toggleStatus({{ $subscriber->id }})"
                                                            title="{{ $subscriber->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="zmdi {{ $subscriber->is_active ? 'zmdi-pause' : 'zmdi-play' }}"></i>
                                                    </button>
                                                    <a href="{{ route('admin.subscribers.edit', $subscriber->id) }}"
                                                       class="btn btn-secondary btn-sm" title="Edit">
                                                        <i class="zmdi zmdi-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-danger btn-sm"
                                                            onclick="deleteSubscriber({{ $subscriber->id }})" title="Delete">
                                                        <i class="zmdi zmdi-delete"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            {{ $subscribers->withQueryString()->links() }}
                        @else
                            <div class="text-center py-5">
                                <i class="zmdi zmdi-accounts zmdi-hc-3x text-muted mb-3"></i>
                                <h5>No Subscribers Found</h5>
                                <p class="text-muted">No subscribers match your search criteria.</p>
                            </div>
                        @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
<script>
    // Toggle bulk actions
    function toggleBulkActions() {
        $('#bulkActions').slideToggle();
    }

    // Select all checkboxes
    $(document).on('change', '#selectAll', function() {
        $('.subscriber-checkbox').prop('checked', this.checked);
        updateSelectedCount();
    });

    // Update selected count
    $(document).on('change', '.subscriber-checkbox', function() {
        updateSelectedCount();
    });

    function updateSelectedCount() {
        const count = $('.subscriber-checkbox:checked').length;
        $('#selectedCount').text(count + ' selected');
    }

    // Toggle subscriber status
    function toggleStatus(id) {
        $.ajax({
            url: `/admin/subscribers/${id}/toggle-status`,
            method: 'POST',
            success: function(response) {
                AdminUI.toast(response.message || 'Subscriber updated.');
                AdminUI.refresh();
            },
            error: function() {
                AdminUI.toast('Error updating subscriber status', 'error');
            }
        });
    }

    // Delete subscriber
    function deleteSubscriber(id) {
        if (confirm('Are you sure you want to delete this subscriber?')) {
            $.ajax({
                url: `/admin/subscribers/${id}`,
                method: 'POST',
                data: {_method: 'DELETE'},
                success: function() {
                    AdminUI.toast('Subscriber deleted.');
                    AdminUI.refresh();
                },
                error: function() {
                    AdminUI.toast('Error deleting subscriber', 'error');
                }
            });
        }
    }

    // Bulk actions
    function bulkAction(action) {
        const selectedIds = $('.subscriber-checkbox:checked').map(function() {
            return this.value;
        }).get();

        if (selectedIds.length === 0) {
            AdminUI.toast('Please select at least one subscriber', 'error');
            return;
        }

        let message = '';
        switch(action) {
            case 'activate':
                message = 'activate the selected subscribers';
                break;
            case 'deactivate':
                message = 'deactivate the selected subscribers';
                break;
            case 'delete':
                message = 'delete the selected subscribers';
                break;
        }

        if (confirm(`Are you sure you want to ${message}?`)) {
            $.ajax({
                url: '/admin/subscribers/bulk-action',
                method: 'POST',
                data: {
                    action: action,
                    subscriber_ids: selectedIds
                },
                success: function(response) {
                    AdminUI.toast(response.message || 'Done.');
                    AdminUI.refresh().done(function () {
                        $('#selectAll').prop('checked', false);
                        updateSelectedCount();
                    });
                },
                error: function() {
                    AdminUI.toast('Error performing bulk action', 'error');
                }
            });
        }
    }

    // Export subscribers
    function exportSubscribers() {
        const search = $('input[name="search"]').val();
        const status = $('select[name="status"]').val();

        let url = '/admin/subscribers/export?';
        if (search) url += 'search=' + encodeURIComponent(search) + '&';
        if (status) url += 'status=' + encodeURIComponent(status);

        window.location.href = url;
    }
</script>
@endpush
