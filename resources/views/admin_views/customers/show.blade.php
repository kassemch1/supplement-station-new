@extends('layouts.admin')

@section('title', $customer['name'] . ' · Customer')

@php
    $money = fn ($v) => '$' . number_format((float) $v, 2);
    $zone = config('app.display_timezone');
@endphp

@section('content')
        <div class="row justify-content-between align-items-center mb-10">
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <a href="{{ route('admin.customers.index') }}" class="cu-back">← All customers</a>
                    <h3><bdi>{{ $customer['name'] }}</bdi>
                        @if($customer['orders'] >= \App\Http\Controllers\Admin\CustomerController::REGULAR_ORDERS)
                            <span class="badge badge-primary">Regular</span>
                        @endif
                    </h3>
                    <p class="mb-0"><bdi>{{ $customer['phone'] }}</bdi>
                        @if($customer['cities']->isNotEmpty()) · <bdi>{{ $customer['cities']->join(', ') }}</bdi>@endif</p>
                </div>
            </div>
        </div>

        <div class="db-grid db-grid--kpi cu-stats">
            <div class="db-card cu-stat"><span class="db-kpi__label">Orders</span><strong>{{ $customer['orders'] }}</strong>
                <small>{{ $customer['cancelled'] ? $customer['cancelled'] . ' cancelled, not counted' : 'none cancelled' }}</small></div>
            <div class="db-card cu-stat"><span class="db-kpi__label">Spent</span><strong>{{ $money($customer['spent']) }}</strong>
                <small>{{ $money($customer['average']) }} per order</small></div>
            <div class="db-card cu-stat"><span class="db-kpi__label">First order</span><strong>{{ $customer['first']->timezone($zone)->format('M j, Y') }}</strong>
                <small>{{ $customer['first']->diffForHumans() }}</small></div>
            <div class="db-card cu-stat {{ $customer['last']->diffInDays(now()) >= \App\Http\Controllers\Admin\CustomerController::LAPSED_DAYS ? 'cu-stat--warn' : '' }}">
                <span class="db-kpi__label">Last order</span><strong>{{ $customer['last']->timezone($zone)->format('M j, Y') }}</strong>
                <small>{{ $customer['last']->diffForHumans() }}</small></div>
        </div>

        <div class="db-grid db-grid--2-1">
            <div class="box">
                <div class="box-head"><h4 class="title">Orders</h4></div>
                <div class="box-body">
                    <table class="table cu-table">
                        <thead><tr><th>Order</th><th>Date</th><th>Status</th><th class="text-right">Items</th><th class="text-right">Total</th></tr></thead>
                        <tbody>
                            @foreach($orders as $order)
                                <tr class="cu-row" data-href="{{ route('admin.orders.details', $order->id) }}">
                                    <td><a href="{{ route('admin.orders.details', $order->id) }}" class="cu-name">#{{ $order->id }}</a></td>
                                    <td>{{ $order->created_at->timezone($zone)->format('M j, Y · H:i') }}</td>
                                    <td><span class="badge badge-{{ ['pending' => 'warning', 'delivered' => 'success', 'cancelled' => 'secondary'][$order->status] ?? 'info' }}">{{ ucfirst($order->status) }}</span></td>
                                    <td class="text-right">{{ $order->order_items_count }}</td>
                                    <td class="text-right"><strong>{{ $money($order->total_amount) }}</strong></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box">
                <div class="box-head"><h4 class="title">Details</h4></div>
                <div class="box-body">
                    <dl class="cu-details">
                        <dt>Names used</dt><dd>@foreach($customer['names'] as $name)<bdi>{{ $name }}</bdi>@if(!$loop->last), @endif @endforeach</dd>
                        <dt>Email</dt><dd>{{ $customer['emails']->isNotEmpty() ? $customer['emails']->join(', ') : 'Not given' }}</dd>
                        <dt>Newsletter</dt><dd>{{ $customer['subscribed'] ? 'Subscribed' : 'Not subscribed' }}</dd>
                        <dt>Latest address</dt><dd><bdi>{{ $orders->first()->shippingDetail->address ?: '—' }}</bdi></dd>
                    </dl>
                </div>
            </div>
        </div>
@endsection

@push('scripts')
<script>
    $(document).on('click', '.cu-row', function (e) {
        if (!$(e.target).closest('a, button').length && !window.getSelection().toString()) {
            location.href = this.getAttribute('data-href');
        }
    });
</script>
@endpush
