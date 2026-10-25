@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
        <!-- Page Headings Start -->
        <div class="row justify-content-between align-items-center mb-10">

            <!-- Page Heading Start -->
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Dashboard <span>/ eCommerce</span></h3>
                </div>
            </div><!-- Page Heading End -->

            <!-- Page Button Group Start -->

        </div><!-- Page Headings End -->

        <!-- Top Report Wrap Start -->
        <div class="row">
            <!-- Top Report Start -->
            <div class="col-xlg-3 col-md-6 col-12 mb-30">
                <div class="top-report">

                    <!-- Head -->
                    <div class="head">
                        <h4>Total Income</h4>
                        <a href="#" class="view"><i class="zmdi zmdi-eye"></i></a>
                    </div>

                    <div class="content">
                        <h5>This Month</h5>
                        <h2>${{ number_format($thisMonthTotal, 2) }}</h2>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <div class="progess">
                            <div class="progess-bar" style="width: {{ $thisMonthTotal > 0 ? 100 : 0 }}%;"></div>
                        </div>
                        @if($monthlyGrowthPercentage != 0)
                            <p style="color: {{ $isGrowthPositive ? 'green' : 'red' }};">
                                {{ $isGrowthPositive ? '↑' : '↓' }}
                                {{ abs($monthlyGrowthPercentage) }}% vs last month
                            </p>
                        @else
                            <p>No change from last month</p>
                        @endif
                    </div>

                </div>
            </div><!-- Top Report End -->
            <!-- Top Report Start - Last Month Income -->
            <div class="col-xlg-3 col-md-6 col-12 mb-30">
                <div class="top-report">
                    <!-- Head -->
                    <div class="head">
                        <h4>Total Income</h4>
                        <a href="#" class="view"><i class="zmdi zmdi-eye"></i></a>
                    </div>

                    <!-- Content -->
                    <div class="content">
                        <h5>Last Month</h5>
                        <h2>${{ number_format($lastMonthTotal, 2) }}</h2>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <div class="progess">
                            <div class="progess-bar" style="width: {{ $lastMonthTotal > 0 ? 100 : 0 }}%;"></div>
                        </div>
                        <p>Previous month income</p>
                    </div>
                </div>
            </div><!-- Top Report End -->
            <!-- Top Report Start - All Time Income -->
            <div class="col-xlg-3 col-md-6 col-12 mb-30">
                <div class="top-report">
                    <!-- Head -->
                    <div class="head">
                        <h4>Total Income</h4>
                        <a href="#" class="view"><i class="zmdi zmdi-eye"></i></a>
                    </div>

                    <!-- Content -->
                    <div class="content">
                        <h5>All Time</h5>
                        <h2>${{ number_format($totalAmount, 2) }}</h2>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <div class="progess">
                            <div class="progess-bar" style="width: 100%;"></div>
                        </div>
                        <p>Total lifetime income</p>
                    </div>
                </div>
            </div><!-- Top Report End -->
            <!-- Top Report Start - Growth Indicator -->
            <div class="col-xlg-3 col-md-6 col-12 mb-30">
                <div class="top-report">
                    <!-- Head -->
                    <div class="head">
                        <h4>Monthly Growth</h4>
                        <a href="#" class="view"><i class="zmdi zmdi-eye"></i></a>
                    </div>

                    <!-- Content -->
                    <div class="content">
                        <h5>This vs Last Month</h5>
                        <h2 style="color: {{ $isGrowthPositive ? 'green' : 'red' }};">
                            {{ $isGrowthPositive ? '+' : '' }}{{ $monthlyGrowthPercentage }}%
                        </h2>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <div class="progess">
                            <div class="progess-bar"
                                 style="width: {{ min(100, abs($monthlyGrowthPercentage)) }}%; background-color: {{ $isGrowthPositive ? 'green' : 'red' }};"></div>
                        </div>
                        <p>
                            {{ $isGrowthPositive ? 'Growth' : 'Decline' }}:
                            ${{ number_format(abs($monthlyGrowth), 2) }}
                        </p>
                    </div>
                </div>
            </div><!-- Top Report End -->
            <!-- Top Report Start -->
            <div class="col-xlg-3 col-md-6 col-12 mb-30">
                <div class="top-report">

                    <!-- Head -->
                    <div class="head">
                        <h4>Pending Orders</h4>
                        <a href="#" class="view"><i class="zmdi zmdi-eye"></i></a>
                    </div>

                    <!-- Content -->
                    <div class="content">
                        <h5>All Pending Orders</h5>
                        <h2>{{$pendingCount}}</h2>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <div class="progess">
                            <div class="progess-bar" style="width: {{$pendingPercentage}}%;"></div>
                        </div>
                        <p>{{$pendingPercentage}}% of orders pending </p>
                    </div>

                </div>
            </div><!-- Top Report End -->

            <!-- Top Report Start -->
            <div class="col-xlg-3 col-md-6 col-12 mb-30">
                <div class="top-report">

                    <!-- Head -->
                    <div class="head">
                        <h4>Delivered Orders</h4>
                        <a href="#" class="view"><i class="zmdi zmdi-eye"></i></a>
                    </div>

                    <!-- Content -->
                    <div class="content">
                        <h5>All Delivered Orders</h5>
                        <h2>{{$deliveredCount}}</h2>
                    </div>

                    <!-- Footer -->
                    <div class="footer">
                        <div class="progess">
                            <div class="progess-bar" style="width: {{$deliveredPercentage}}%;"></div>
                        </div>
                        <p>{{$deliveredPercentage}}% of orders delivered </p>
                    </div>

                </div>
            </div><!-- Top Report End -->
            <div class="col-xlg-3 col-md-6 col-12 mb-30">
                <div class="top-report">
                    <div class="head">
                        <h4>Today's Performance</h4>
                        <a href="#" class="view"><i class="zmdi zmdi-eye"></i></a>
                    </div>
                    <div class="content">
                        <h5>Orders Today</h5>
                        <h2>{{ $todayOrders }}</h2>
                        <p style="color: #666;">Revenue: ${{ number_format($todayRevenue, 2) }}</p>
                    </div>
                    <div class="footer">
                        <div class="progess">
                            <div class="progess-bar" style="width: {{ min(100, $todayOrders * 10) }}%;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Weekly Performance Card -->
            <div class="col-xlg-3 col-md-6 col-12 mb-30">
                <div class="top-report">
                    <div class="head">
                        <h4>Weekly Growth</h4>
                        <a href="#" class="view"><i class="zmdi zmdi-eye"></i></a>
                    </div>
                    <div class="content">
                        <h5>This Week</h5>
                        <h2 style="color: {{ $isWeeklyGrowthPositive ? 'green' : 'red' }};">
                            {{ $isWeeklyGrowthPositive ? '+' : '' }}{{ $weeklyGrowthPercentage }}%
                        </h2>
                    </div>
                    <div class="footer">
                        <div class="progess">
                            <div class="progess-bar"
                                 style="width: {{ min(100, abs($weeklyGrowthPercentage)) }}%; background-color: {{ $isWeeklyGrowthPositive ? 'green' : 'red' }};"></div>
                        </div>
                        <p>${{ number_format($thisWeekRevenue, 2) }} this week</p>
                    </div>
                </div>
            </div>
        </div><!-- Top Report Wrap End -->

        <div class="row mbn-30">

            <!-- Market Trends Chart Start -->
            <div class="col-md-12 mb-30">
                <div class="box">
                    <div class="box-head">
                        <h4 class="title">Market Trends</h4>
                    </div>
                    <div class="box-body">
                        <div class="chartjs-market-trends-chart">
                            <canvas id="chartjs-market-trends-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div><!-- Market Trends Chart End -->

        </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    // Revenue per month, store time (see DashboardController::monthlyChart)
    var months = @json($months);
    var totals = @json($totals);

    // Chart.js configuration
    var ctx = document.getElementById('chartjs-market-trends-chart').getContext('2d');
    var marketTrendsChart = new Chart(ctx, {
        type: 'line', // Line chart to show trends over time
        data: {
            labels: months, // The x-axis labels will be the months
            datasets: [{
                label: 'Total Amount per Month',
                data: totals, // The y-axis data will be the monthly totals
                backgroundColor: 'rgba(75, 192, 192, 0.2)', // Line fill color
                borderColor: 'rgba(75, 192, 192, 1)', // Line color
                borderWidth: 2,
                fill: true, // Fill the area under the line
                tension: 0.4 // Smooth the line
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top', // Position the legend at the top
                }
            },
            scales: {
                y: {
                    beginAtZero: true, // Ensure the y-axis starts from 0
                    ticks: {
                        callback: function(value) {
                            return '$' + value.toLocaleString(); // Format y-axis ticks as currency
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
