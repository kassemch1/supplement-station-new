@extends('layouts.admin')

@section('title', 'Dashboard')

@php
    $money = fn ($v) => '$' . number_format((float) $v, 2);
    $k = $report['kpis'];
    $statusColors = ['pending' => '#f59e0b', 'delivered' => '#22c55e', 'cancelled' => '#94a3b8'];
    $statusLinks = ['pending' => route('admin.orders.pending'), 'delivered' => route('admin.orders.delivered'), 'cancelled' => route('admin.orders.cancelled')];
    $statusTotal = array_sum($report['status']);
    $maxProduct = max(array_column($report['products'], 'revenue') ?: [0]);
    $maxCity = max(array_column($report['cities'], 'orders') ?: [0]);
@endphp

@section('content')
    {{-- title and the period filter --}}
    <div class="db-head">
        <div>
            <h3>Dashboard</h3>
            <p>
                Showing <strong>{{ $report['label'] }}</strong>
                @if($report['compare']) · compared with {{ $report['compare'] }} @endif
            </p>
        </div>
        <div class="db-filter">
            <nav class="db-seg" aria-label="Period">
                @foreach($ranges as $key => $name)
                    <a href="{{ route('admin', ['range' => $key]) }}" class="{{ $report['range'] === $key ? 'is-on' : '' }}">{{ $name }}</a>
                @endforeach
            </nav>
            <form class="db-custom {{ $report['range'] === 'custom' ? 'is-on' : '' }}" method="GET" action="{{ route('admin') }}">
                <input type="hidden" name="range" value="custom">
                <input type="date" name="from" value="{{ $report['from'] }}" aria-label="From" required>
                <span>to</span>
                <input type="date" name="to" value="{{ $report['to'] }}" aria-label="To" required>
                <button type="submit">Apply</button>
            </form>
            @if($report['range'] !== '30d')
                <a href="{{ route('admin') }}" class="btn btn-secondary db-clear"><i class="zmdi zmdi-close"></i> Clear filters</a>
            @endif
        </div>
    </div>

    {{-- the four numbers for the period --}}
    @php
        $cards = [
            ['key' => 'revenue', 'label' => 'Revenue', 'value' => $money($k['revenue']['value']), 'prev' => $money($k['revenue']['prev']), 'icon' => 'zmdi-money', 'tone' => 'brand'],
            ['key' => 'orders', 'label' => 'Orders', 'value' => number_format($k['orders']['value']), 'prev' => number_format($k['orders']['prev']), 'icon' => 'zmdi-shopping-cart', 'tone' => 'blue'],
            ['key' => 'average', 'label' => 'Average order', 'value' => $money($k['average']['value']), 'prev' => $money($k['average']['prev']), 'icon' => 'zmdi-receipt', 'tone' => 'green'],
            ['key' => 'items', 'label' => 'Items sold', 'value' => number_format($k['items']['value']), 'prev' => number_format($k['items']['prev']), 'icon' => 'zmdi-labels', 'tone' => 'amber'],
        ];
    @endphp
    <div class="db-grid db-grid--kpi">
        @foreach($cards as $card)
            @php $d = $k[$card['key']]['delta']; @endphp
            <div class="db-card db-kpi">
                <div class="db-kpi__top">
                    <span class="db-kpi__label">{{ $card['label'] }}</span>
                    <span class="db-kpi__icon db-kpi__icon--{{ $card['tone'] }}"><i class="zmdi {{ $card['icon'] }}"></i></span>
                </div>
                <div class="db-kpi__value">{{ $card['value'] }}</div>
                <div class="db-kpi__foot">
                    @if($d === null)
                        <span>{{ $report['label'] }}</span>
                    @else
                        <span class="db-delta {{ $d > 0 ? 'db-delta--up' : ($d < 0 ? 'db-delta--down' : 'db-delta--flat') }}">
                            {{ $d > 0 ? '▲' : ($d < 0 ? '▼' : '•') }} {{ number_format(abs($d), 1) }}%
                        </span>
                        <span>vs {{ $card['prev'] }} before</span>
                    @endif
                </div>
                <div class="db-spark"><canvas data-spark="{{ $card['key'] }}"></canvas></div>
            </div>
        @endforeach
    </div>

    {{-- sold out: what customers cannot buy right now, best sellers first (not affected by the filter) --}}
    @if($soldOut['count'] > 0)
        <div class="db-card db-soldout" data-soldout>
            <div class="db-card__head">
                <div>
                    <h4>Sold out <span class="db-soldout__count" data-soldout-count>{{ $soldOut['count'] }}</span></h4>
                    <small>Best sellers of the last 90 days first · flip the switch when a product is back</small>
                </div>
                <a href="{{ route('manageProducts.soldOut') }}">See all sold-out products</a>
            </div>
            <ul class="db-soldout__list">
                @foreach($soldOut['items'] as $product)
                    <li class="db-soldout__item">
                        <a class="db-soldout__img" href="{{ route('manageProducts.edit', ['id' => $product->id]) }}">
                            @if($product->images->isNotEmpty())
                                <img src="{{ asset($product->images->first()->url) }}" alt="" loading="lazy" width="44" height="44">
                            @endif
                        </a>
                        <span class="db-soldout__info">
                            <a href="{{ route('manageProducts.edit', ['id' => $product->id]) }}" class="db-soldout__name">{{ $product->name }}</a>
                            <small>
                                Sold out {{ ($product->sold_out_at ?? $product->updated_at)?->diffForHumans(['parts' => 1, 'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE]) ?? '' }}
                                @if($product->sold_recently > 0)
                                    · <strong>{{ $product->sold_recently }} sold</strong> in 90 days
                                @endif
                            </small>
                        </span>
                        @include('admin_views.partials.stock_switch', ['product' => $product])
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- sales over time + order status --}}
    <div class="db-grid db-grid--2-1">
        <div class="db-card">
            <div class="db-card__head">
                <div>
                    <h4>Sales over time</h4>
                    <small>{{ ['hour' => 'Per hour', 'day' => 'Per day', 'month' => 'Per month'][$report['unit']] }} · {{ $report['label'] }}</small>
                </div>
                <div class="db-tabs" id="salesTabs">
                    <button type="button" class="is-on" data-metric="revenue">Revenue</button>
                    <button type="button" data-metric="orders">Orders</button>
                    <button type="button" data-metric="items">Items</button>
                </div>
            </div>
            <div class="db-chart"><canvas id="salesChart"></canvas></div>
        </div>

        <div class="db-card">
            <div class="db-card__head">
                <div>
                    <h4>Order status</h4>
                    <small>{{ number_format($statusTotal) }} orders · {{ $report['label'] }}</small>
                </div>
            </div>
            @if($statusTotal)
                <div class="db-chart db-chart--sm"><canvas id="statusChart"></canvas></div>
            @else
                <p class="db-empty">No orders in this period.</p>
            @endif
            <div class="db-status">
                @foreach($report['status'] as $name => $count)
                    <a class="db-status__row" href="{{ $statusLinks[$name] }}">
                        <span><span class="db-dot" style="background: {{ $statusColors[$name] }}"></span>{{ ucfirst($name) }}</span>
                        <span><strong>{{ number_format($count) }}</strong> · {{ $statusTotal ? number_format($count / $statusTotal * 100, 1) : 0 }}%</span>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- best sellers + cities --}}
    <div class="db-grid db-grid--1-1">
        <div class="db-card">
            <div class="db-card__head">
                <div><h4>Best sellers</h4><small>By revenue · {{ $report['label'] }}</small></div>
                <a href="{{ route('manageProducts.index') }}">All products</a>
            </div>
            @if($report['products'])
                <ol class="db-rank">
                    @foreach($report['products'] as $i => $p)
                        <li>
                            <span class="db-rank__n">{{ $i + 1 }}</span>
                            <span class="db-rank__name" title="{{ $p['name'] }}">{{ $p['name'] }} <span class="db-rank__sub">· {{ $p['qty'] }} sold</span></span>
                            <span class="db-rank__val">{{ $money($p['revenue']) }}</span>
                            <span class="db-rank__bar"><span style="width: {{ $maxProduct > 0 ? max(2, $p['revenue'] / $maxProduct * 100) : 0 }}%"></span></span>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="db-empty">Nothing sold in this period.</p>
            @endif
        </div>

        <div class="db-card">
            <div class="db-card__head">
                <div><h4>Top cities</h4><small>By orders · {{ $report['label'] }}</small></div>
            </div>
            @if($report['cities'])
                <ol class="db-rank">
                    @foreach($report['cities'] as $i => $c)
                        <li>
                            <span class="db-rank__n">{{ $i + 1 }}</span>
                            <span class="db-rank__name" title="{{ $c['name'] }}">{{ $c['name'] }} <span class="db-rank__sub">· {{ $money($c['revenue']) }}</span></span>
                            <span class="db-rank__val">{{ $c['orders'] }} {{ $c['orders'] === 1 ? 'order' : 'orders' }}</span>
                            <span class="db-rank__bar"><span style="width: {{ $maxCity > 0 ? max(2, $c['orders'] / $maxCity * 100) : 0 }}%"></span></span>
                        </li>
                    @endforeach
                </ol>
            @else
                <p class="db-empty">No orders in this period.</p>
            @endif
        </div>
    </div>

    {{-- busiest times + every month --}}
    <div class="db-grid db-grid--1-1">
        <div class="db-card">
            <div class="db-card__head">
                <div><h4>When orders come in</h4><small>Store time · {{ $report['label'] }}</small></div>
                <div class="db-tabs" id="timeTabs">
                    <button type="button" class="is-on" data-view="weekday">Day of week</button>
                    <button type="button" data-view="hour">Hour of day</button>
                </div>
            </div>
            <div class="db-chart db-chart--sm"><canvas id="timeChart"></canvas></div>
        </div>

        <div class="db-card">
            <div class="db-card__head">
                <div><h4>Revenue by month</h4><small>Every month since the first sale</small></div>
            </div>
            <div class="db-chart db-chart--sm"><canvas id="monthsChart"></canvas></div>
        </div>
    </div>

    {{-- latest orders + all-time numbers --}}
    <div class="db-grid db-grid--2-1">
        <div class="db-card">
            <div class="db-card__head">
                <div><h4>Latest orders</h4><small>Click an order to open it</small></div>
                <a href="{{ route('admin.orders.pending') }}">Pending orders</a>
            </div>
            @if($recentOrders->isNotEmpty())
                <div class="db-scroll">
                    <table class="db-orders">
                        <thead><tr><th>Order</th><th>Customer</th><th>City</th><th>Date</th><th>Status</th><th class="db-num">Total</th></tr></thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                                <tr onclick="location.href='{{ route('admin.orders.details', $order->id) }}'">
                                    <td><a href="{{ route('admin.orders.details', $order->id) }}">#{{ $order->id }}</a></td>
                                    <td>{{ $order->shippingDetail->name ?? '—' }}</td>
                                    <td>{{ $order->shippingDetail->city ?? '—' }}</td>
                                    <td>{{ $order->created_at?->timezone(config('app.display_timezone'))->format('M d, H:i') }}</td>
                                    <td><span class="badge badge-{{ ['pending' => 'warning', 'delivered' => 'success'][$order->status] ?? 'secondary' }}">{{ ucfirst($order->status) }}</span></td>
                                    <td class="db-num">{{ $money($order->total_amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="db-empty">No orders yet.</p>
            @endif
        </div>

        <div class="db-card">
            <div class="db-card__head"><div><h4>At a glance</h4><small>Not affected by the filter</small></div></div>
            <div class="db-mini" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                <div><span>Today</span><strong>{{ $money($todayRevenue) }}</strong><small>{{ $todayOrders }} {{ $todayOrders === 1 ? 'order' : 'orders' }}</small></div>
                <div><span>This week</span><strong>{{ $money($thisWeekRevenue) }}</strong>
                    <small class="{{ $isWeeklyGrowthPositive ? 'text-success' : 'text-danger' }}">{{ $isWeeklyGrowthPositive ? '▲' : '▼' }} {{ abs($weeklyGrowthPercentage) }}% vs last week</small></div>
                <div><span>This month</span><strong>{{ $money($thisMonthTotal) }}</strong>
                    <small class="{{ $isGrowthPositive ? 'text-success' : 'text-danger' }}">{{ $isGrowthPositive ? '▲' : '▼' }} {{ abs($monthlyGrowthPercentage) }}% vs last month</small></div>
                <div><span>Last month</span><strong>{{ $money($lastMonthTotal) }}</strong><small>&nbsp;</small></div>
                <div><span>All-time revenue</span><strong>{{ $money($totalAmount) }}</strong><small>&nbsp;</small></div>
                <div><span>Orders</span><strong>{{ number_format($deliveredCount) }}</strong><small>delivered · {{ $pendingCount }} pending</small></div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var report = @json($report);
    var months = @json($months);
    var totals = @json($totals);
    var statusColors = @json($statusColors);
    var charts = [];

    function css(name) { return getComputedStyle(document.documentElement).getPropertyValue(name).trim(); }
    function money(v) { return '$' + Number(v).toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 2}); }
    function palette() {
        return {
            brand: css('--ad-brand-text'), brandFill: css('--ad-brand-soft'),
            text: css('--ad-text-2'), muted: css('--ad-muted'), grid: css('--ad-border'),
            surface: css('--ad-surface'), blue: '#3b82f6', green: '#22c55e', amber: '#f59e0b'
        };
    }

    Chart.defaults.font.family = 'Inter, "Segoe UI", Roboto, sans-serif';
    Chart.defaults.font.size = 12;
    Chart.defaults.maintainAspectRatio = false;
    Chart.defaults.plugins.tooltip.padding = 10;
    Chart.defaults.plugins.tooltip.cornerRadius = 8;
    Chart.defaults.plugins.tooltip.displayColors = false;

    function axes(p, isMoney) {
        return {
            x: {grid: {display: false}, border: {display: false}, ticks: {color: p.muted, maxRotation: 0, autoSkip: true, autoSkipPadding: 14}},
            y: {beginAtZero: true, grid: {color: p.grid}, border: {display: false},
                ticks: {color: p.muted, precision: 0, callback: function (v) { return isMoney ? money(v) : v; }}}
        };
    }

    // ---- sparklines in the four cards
    var sparkData = {revenue: report.series.revenue, orders: report.series.orders, items: report.series.items,
        average: report.series.revenue.map(function (r, i) { return report.series.orders[i] ? r / report.series.orders[i] : 0; })};
    document.querySelectorAll('[data-spark]').forEach(function (canvas) {
        var p = palette();
        charts.push({kind: 'spark', chart: new Chart(canvas, {
            type: 'line',
            data: {labels: report.series.labels, datasets: [{data: sparkData[canvas.dataset.spark], borderColor: p.brand, backgroundColor: p.brandFill, fill: true, borderWidth: 2, pointRadius: 0, tension: .35}]},
            options: {plugins: {legend: {display: false}, tooltip: {enabled: false}}, scales: {x: {display: false}, y: {display: false, beginAtZero: true}}, animation: false}
        })});
    });

    // ---- sales over time
    var metric = 'revenue';
    var salesCanvas = document.getElementById('salesChart');
    function salesDataset(p) {
        var isMoney = metric === 'revenue';
        return {
            type: report.series.labels.length > 45 ? 'line' : 'bar',
            label: {revenue: 'Revenue', orders: 'Orders', items: 'Items sold'}[metric],
            data: report.series[metric],
            backgroundColor: report.series.labels.length > 45 ? p.brandFill : p.brand,
            borderColor: p.brand,
            borderWidth: report.series.labels.length > 45 ? 2 : 0,
            borderRadius: 6,
            maxBarThickness: 34,
            fill: true,
            tension: .35,
            pointRadius: 0,
            pointHoverRadius: 4
        };
    }
    var sales = new Chart(salesCanvas, {
        data: {labels: report.series.labels, datasets: [salesDataset(palette())]},
        options: {
            interaction: {mode: 'index', intersect: false},
            plugins: {legend: {display: false}, tooltip: {callbacks: {label: function (c) { return c.dataset.label + ': ' + (metric === 'revenue' ? money(c.parsed.y) : c.parsed.y); }}}},
            scales: axes(palette(), true)
        }
    });
    charts.push({kind: 'sales', chart: sales});
    document.querySelectorAll('#salesTabs button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#salesTabs button').forEach(function (b) { b.classList.toggle('is-on', b === btn); });
            metric = btn.dataset.metric;
            restyle();
        });
    });

    // ---- order status
    var statusCanvas = document.getElementById('statusChart');
    if (statusCanvas) {
        var names = Object.keys(report.status);
        charts.push({kind: 'status', chart: new Chart(statusCanvas, {
            type: 'doughnut',
            data: {labels: names.map(function (n) { return n.charAt(0).toUpperCase() + n.slice(1); }),
                datasets: [{data: names.map(function (n) { return report.status[n]; }), backgroundColor: names.map(function (n) { return statusColors[n]; }), borderWidth: 3, borderColor: palette().surface, hoverOffset: 6}]},
            options: {cutout: '68%', plugins: {legend: {display: false}}}
        })});
    }

    // ---- when orders come in
    var timeView = 'weekday';
    var weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
    var hours = Array.from({length: 24}, function (_, h) { return (h < 10 ? '0' : '') + h + ':00'; });
    var time = new Chart(document.getElementById('timeChart'), {
        type: 'bar',
        data: {labels: weekdays, datasets: [{label: 'Orders', data: report.weekday, backgroundColor: palette().blue, borderRadius: 6, maxBarThickness: 30}]},
        options: {plugins: {legend: {display: false}}, scales: axes(palette(), false)}
    });
    charts.push({kind: 'time', chart: time});
    document.querySelectorAll('#timeTabs button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('#timeTabs button').forEach(function (b) { b.classList.toggle('is-on', b === btn); });
            timeView = btn.dataset.view;
            time.data.labels = timeView === 'hour' ? hours : weekdays;
            time.data.datasets[0].data = timeView === 'hour' ? report.hour : report.weekday;
            time.update();
        });
    });

    // ---- revenue by month, all time
    var monthsChart = new Chart(document.getElementById('monthsChart'), {
        type: 'bar',
        data: {labels: months.map(function (m) { var p = m.split(' '); return p[0].slice(0, 3) + ' ' + p[1].slice(2); }),
            datasets: [{label: 'Revenue', data: totals, backgroundColor: palette().green, borderRadius: 5, maxBarThickness: 26}]},
        options: {plugins: {legend: {display: false}, tooltip: {callbacks: {title: function (c) { return months[c[0].dataIndex]; }, label: function (c) { return money(c.parsed.y); }}}}, scales: axes(palette(), true)}
    });
    charts.push({kind: 'months', chart: monthsChart});

    // ---- colours follow the dark / light switch
    function restyle() {
        var p = palette();
        charts.forEach(function (c) {
            var ch = c.chart;
            if (c.kind === 'spark') {
                ch.data.datasets[0].borderColor = p.brand;
                ch.data.datasets[0].backgroundColor = p.brandFill;
            } else if (c.kind === 'status') {
                ch.data.datasets[0].borderColor = p.surface;
            } else {
                if (c.kind === 'sales') ch.data.datasets[0] = salesDataset(p);
                ch.options.scales = axes(p, c.kind === 'sales' ? metric === 'revenue' : c.kind === 'months');
            }
            ch.update();
        });
    }
    document.addEventListener('admin-theme-change', restyle);
})();
</script>
<script>
    // Sold-out card: a product switched back in stock leaves the list (unless switched off again quickly)
    $(document).on('stock:changed', '[data-soldout] [data-stock-toggle]', function (e, inStock) {
        var $item = $(this).closest('.db-soldout__item');
        $item.toggleClass('is-back', inStock);
        if (!inStock) {
            return;
        }
        setTimeout(function () {
            if (!$item.hasClass('is-back')) {
                return;
            }
            $item.slideUp(200, function () {
                $item.remove();
                var $count = $('[data-soldout-count]'), left = parseInt($count.text(), 10) - 1;
                $count.text(left);
                if (left <= 0) {
                    $('[data-soldout]').slideUp(200);
                }
            });
        }, 1400);
    });
</script>
@endpush
