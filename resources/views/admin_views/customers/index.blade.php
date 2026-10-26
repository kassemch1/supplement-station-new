@extends('layouts.admin')

@section('title', 'Customers')

@php
    $money = fn ($v) => '$' . number_format((float) $v, 2);
    $zone = config('app.display_timezone');
    $date = fn ($v) => $v ? \Illuminate\Support\Carbon::parse($v, 'UTC')->timezone($zone) : null;
@endphp

@section('content')
        <div class="row justify-content-between align-items-center mb-10">
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Customers</h3>
                    <p class="text-muted mb-0">Everyone who ordered, grouped by phone number. Cancelled orders are not counted.</p>
                </div>
            </div>
        </div>

        <div class="db-grid db-grid--kpi cu-stats">
            <a class="db-card cu-stat" href="{{ route('admin.customers.index') }}">
                <span class="db-kpi__label">Customers</span>
                <strong>{{ number_format($stats['total']) }}</strong>
                <small>{{ $money($stats['average']) }} spent on average</small>
            </a>
            <a class="db-card cu-stat" href="{{ route('admin.customers.index', ['segment' => 'regulars']) }}">
                <span class="db-kpi__label">Came back</span>
                <strong>{{ number_format($stats['returning']) }}</strong>
                <small>{{ $stats['returningShare'] }}% ordered twice or more · {{ $stats['regulars'] }} regulars</small>
            </a>
            <a class="db-card cu-stat" href="{{ route('admin.customers.index', ['segment' => 'new']) }}">
                <span class="db-kpi__label">New</span>
                <strong>{{ number_format($stats['new']) }}</strong>
                <small>first order in the last {{ \App\Http\Controllers\Admin\CustomerController::NEW_DAYS }} days</small>
            </a>
            <a class="db-card cu-stat cu-stat--warn" href="{{ route('admin.customers.index', ['segment' => 'lapsed', 'sort' => 'orders']) }}">
                <span class="db-kpi__label">Haven't come back</span>
                <strong>{{ number_format($stats['lapsed']) }}</strong>
                <small>no order in {{ \App\Http\Controllers\Admin\CustomerController::LAPSED_DAYS }}+ days</small>
            </a>
        </div>

        <div class="box">
            <div class="box-head">
                <form action="{{ route('admin.customers.index') }}" method="GET" class="form-inline cu-filter" data-ajax-filter>
                    <input type="search" name="search" class="form-control" value="{{ $search }}" placeholder="Search name or phone" aria-label="Search customers by name or phone" autocomplete="off">
                    <select name="segment" class="form-control" aria-label="Which customers">
                        @foreach(\App\Http\Controllers\Admin\CustomerController::SEGMENTS as $key => $label)
                            <option value="{{ $key }}" @selected($segment === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <select name="sort" class="form-control" data-clear-to="recent" aria-label="Order of the list">
                        @foreach(\App\Http\Controllers\Admin\CustomerController::SORTS as $key => $label)
                            <option value="{{ $key }}" @selected($sort === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </form>
            </div>
            <div class="box-body" data-ajax-list="customers">
                <table class="table cu-table">
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Phone</th>
                            <th>City</th>
                            <th class="text-right">Orders</th>
                            <th class="text-right">Spent</th>
                            <th>First order</th>
                            <th>Last order</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                            @php
                                $info = $latest[$customer->phone_key] ?? null;
                                $last = $date($customer->last_at);
                                $days = $last ? (int) $last->diffInDays(now()) : null;
                                $url = route('admin.customers.show', ['phone' => $customer->phone_key]);
                            @endphp
                            <tr class="cu-row" data-href="{{ $url }}">
                                <td>
                                    <a href="{{ $url }}" class="cu-name"><bdi>{{ $info?->name ?: 'Customer' }}</bdi></a>
                                    @if($customer->orders_count >= \App\Http\Controllers\Admin\CustomerController::REGULAR_ORDERS)
                                        <span class="badge badge-primary">Regular</span>
                                    @endif
                                    @if($customer->pending_count > 0)
                                        <span class="badge badge-warning">{{ $customer->pending_count }} pending</span>
                                    @endif
                                </td>
                                <td><a href="tel:{{ $customer->phone_key }}" class="cu-phone">{{ $info?->phone ?? $customer->phone_key }}</a></td>
                                <td><bdi>{{ $info?->city ?: '—' }}</bdi></td>
                                <td class="text-right"><strong>{{ $customer->orders_count }}</strong>@if($customer->cancelled_count) <small class="text-muted">+{{ $customer->cancelled_count }} cancelled</small>@endif</td>
                                <td class="text-right"><strong>{{ $money($customer->spent) }}</strong></td>
                                <td>{{ $date($customer->first_at)?->format('M j, Y') }}</td>
                                <td>
                                    {{ $last?->format('M j, Y') }}
                                    <small class="cu-ago {{ $days !== null && $days >= \App\Http\Controllers\Admin\CustomerController::LAPSED_DAYS ? 'is-lapsed' : '' }}">{{ $last?->diffForHumans(['parts' => 1]) }}</small>
                                </td>
                                <td><a href="{{ $url }}" class="button button-info">Orders</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="ad-empty">
                                {{ $search !== '' || $segment !== '' ? 'No customers match these filters.' : 'No customers yet. They appear here after their first order.' }}
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="d-flex justify-content-center">
                    {{ $customers->links() }}
                </div>
            </div>
        </div>
@endsection

@push('scripts')
<script>
    // a click anywhere on a row opens the customer (links and buttons in it work as usual)
    $(document).on('click', '.cu-row', function (e) {
        if (!$(e.target).closest('a, button').length && !window.getSelection().toString()) {
            location.href = this.getAttribute('data-href');
        }
    });
</script>
@endpush
