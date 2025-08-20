<!doctype html>
<html class="no-js" lang="en">



<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Admin | Company Info </title>
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
    {{--    <link rel="stylesheet" href={{asset("admin_assets/css/style.css")}}>--}}

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

    <!-- Content Body Start -->
    <!-- Content Body Start -->
    <div class="content-body">

        <!-- Page Headings Start -->
        <div class="row justify-content-between align-items-center mb-10">

            <!-- Page Heading Start -->
            <div class="col-12 col-lg-auto mb-20">
                <div class="page-heading">
                    <h3>Dashboard <span>/ eCommerce</span></h3>
                </div>
            </div><!-- Page Heading End -->

            <!-- Page Button Group Start -->
            {{--            <div class="col-12 col-lg-auto mb-20">--}}
            {{--                <div class="page-date-range">--}}
            {{--                    <input type="text" class="form-control input-date-predefined">--}}
            {{--                </div>--}}
            {{--            </div><!-- Page Button Group End -->--}}

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
{{--                        <p>{{ date('F j, Y') }}</p> <!-- Changed this line -->--}}
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

            {{--            <!-- Revenue Statistics Chart Start -->--}}
            {{--            <div class="col-md-8 mb-30">--}}
            {{--                <div class="box">--}}
            {{--                    <div class="box-head">--}}
            {{--                        <h4 class="title">Revenue Statistics</h4>--}}
            {{--                    </div>--}}
            {{--                    <div class="box-body">--}}
            {{--                        <div class="chart-legends-1 row">--}}
            {{--                            <div class="chart-legend-1 col-12 col-sm-4">--}}
            {{--                                <h5 class="title">Total Sale</h5>--}}
            {{--                                <h3 class="value text-secondary">$5000,000</h3>--}}
            {{--                            </div>--}}
            {{--                            <div class="chart-legend-1 col-12 col-sm-4">--}}
            {{--                                <h5 class="title">Total View</h5>--}}
            {{--                                <h3 class="value text-primary">10000,000</h3>--}}
            {{--                            </div>--}}
            {{--                            <div class="chart-legend-1 col-12 col-sm-4">--}}
            {{--                                <h5 class="title">Total Support</h5>--}}
            {{--                                <h3 class="value text-warning">100,000</h3>--}}
            {{--                            </div>--}}
            {{--                        </div>--}}
            {{--                        <div class="chartjs-revenue-statistics-chart">--}}
            {{--                            <canvas id="chartjs-revenue-statistics-chart"></canvas>--}}
            {{--                        </div>--}}
            {{--                    </div>--}}
            {{--                </div>--}}
            {{--            </div><!-- Revenue Statistics Chart End -->--}}

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

    </div><!-- Content Body End -->

    <!-- Footer Section Start -->

    @include('partials.adminFooter')

    <!-- Footer Section End -->

</div>

<!-- JS
============================================ -->

<!-- Global Vendor, plugins & Activation JS -->
<script src={{asset("admin_assets/js/vendor/modernizr-3.6.0.min.js")}}></script>
<script src={{asset("admin_assets/js/vendor/jquery-3.3.1.min.js")}}></script>
<script src={{asset("admin_assets/js/vendor/popper.min.js")}}></script>
<script src={{asset("admin_assets/js/vendor/bootstrap.min.js")}}></script>
<!--Plugins JS-->
<script src={{asset("admin_assets/js/plugins/perfect-scrollbar.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/tippy4.min.js.js")}}></script>
<!--Main JS-->
<script src={{asset("admin_assets/js/main.js")}}></script>

<!-- Plugins & Activation JS For Only This Page -->
<script src={{asset("admin_assets/js/plugins/nice-select/jquery.nice-select.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/nice-select/niceSelect.active.js")}}></script>
<script src={{asset("admin_assets/js/plugins/filepond/filepond.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/filepond/filepond-plugin-image-exif-orientation.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/filepond/filepond-plugin-image-preview.min.js")}}></script>
<script src={{asset("admin_assets/js/plugins/filepond/filepond.active.js")}}></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!--Echarts-->
{{--<script src={{"admin_assets/js/plugins/chartjs/Chart.min.js"}}></script>--}}
{{--<script src={{"admin_assets/js/plugins/chartjs/chartjs.active.js"}}></script>--}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function() {
        $('#companyInfoForm').submit(function(e) {
            e.preventDefault(); // Prevent default form submission

            // Serialize form data
            var formData = $(this).serialize();

            // Send AJAX request
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: 'PUT',
                url: $(this).attr('action'), // Use form's action attribute as URL
                data: formData,
                success: function(response) {
                    $('#successAlert').fadeIn();
                    setTimeout(function() {
                        $('#successAlert').fadeOut();
                    }, 3000);
                },
                error: function(xhr, status, error) {
                    $('#errorAlert').fadeIn();
                    setTimeout(function() {
                        $('#errorAlert').fadeOut();
                    }, 3000);
                }
            });
        });
    });
    // Ensure that the variables are correctly converted to JSON
    var months = @json($months); // Array of months (e.g., ['January 2025', 'December 2024', ...])
    var totals = @json($totals); // Array of totals for each month

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


</body>



</html>
