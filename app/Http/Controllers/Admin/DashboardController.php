<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

// Sales exclude cancelled orders. Dates are stored in UTC, while "today", "this week" and the chart's
// months are store-time (Beirut) periods.
class DashboardController extends Controller
{
    public function index()
    {
        $zone = config('app.display_timezone');
        $now = Carbon::now($zone);
        $lastWeek = $now->copy()->subWeek();
        $lastMonth = $now->copy()->subMonthNoOverflow();

        $periods = [
            'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
            'this_week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek()],
            'last_week' => [$lastWeek->copy()->startOfWeek(), $lastWeek->copy()->endOfWeek()],
            'this_month' => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            'last_month' => [$lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth()],
        ];

        // Every number on the cards in one pass over the orders
        $columns = [
            "COUNT(*) as orders",
            "SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending",
            "SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered",
            "SUM(CASE WHEN status != 'cancelled' THEN total_amount ELSE 0 END) as revenue_all",
            "SUM(CASE WHEN status != 'cancelled' AND created_at BETWEEN ? AND ? THEN 1 ELSE 0 END) as orders_today",
        ];
        $bindings = [$periods['today'][0]->copy()->utc(), $periods['today'][1]->copy()->utc()];
        foreach ($periods as $name => [$from, $to]) {
            $columns[] = "SUM(CASE WHEN status != 'cancelled' AND created_at BETWEEN ? AND ? THEN total_amount ELSE 0 END) as revenue_{$name}";
            array_push($bindings, $from->copy()->utc(), $to->copy()->utc());
        }
        $stats = DB::table('orders')->selectRaw(implode(', ', $columns), $bindings)->first();

        $thisMonth = (float) $stats->revenue_this_month;
        $lastMonthTotal = (float) $stats->revenue_last_month;
        $thisWeek = (float) $stats->revenue_this_week;
        $lastWeekTotal = (float) $stats->revenue_last_week;
        $percent = fn (int $part) => $stats->orders > 0 ? round($part / $stats->orders * 100, 2) : 0;

        [$months, $totals] = $this->monthlyChart($zone);

        return view('admin_views.admin_dashboard', [
            'months' => $months,
            'totals' => $totals,
            'totalAmount' => (float) $stats->revenue_all,
            'thisMonthTotal' => $thisMonth,
            'lastMonthTotal' => $lastMonthTotal,
            'monthlyGrowth' => $thisMonth - $lastMonthTotal,
            'monthlyGrowthPercentage' => $this->growth($thisMonth, $lastMonthTotal),
            'isGrowthPositive' => $thisMonth >= $lastMonthTotal,
            'pendingCount' => (int) $stats->pending,
            'deliveredCount' => (int) $stats->delivered,
            'pendingPercentage' => $percent((int) $stats->pending),
            'deliveredPercentage' => $percent((int) $stats->delivered),
            'todayOrders' => (int) $stats->orders_today,
            'todayRevenue' => (float) $stats->revenue_today,
            'thisWeekRevenue' => $thisWeek,
            'lastWeekRevenue' => $lastWeekTotal,
            'weeklyGrowthPercentage' => $this->growth($thisWeek, $lastWeekTotal),
            'isWeeklyGrowthPositive' => $thisWeek >= $lastWeekTotal,
        ]);
    }

    // Revenue per store-time month, from the first sale to this month, months without sales as 0
    private function monthlyChart(string $zone): array
    {
        $byMonth = [];
        $first = null;
        foreach (Order::countsAsSale()->whereNotNull('created_at')->orderBy('created_at')->toBase()->select(['created_at', 'total_amount'])->cursor() as $order) {
            $month = Carbon::parse($order->created_at, 'UTC')->setTimezone($zone)->startOfMonth();
            $first ??= $month;
            $byMonth[$month->format('Y-m')] = ($byMonth[$month->format('Y-m')] ?? 0) + (float) $order->total_amount;
        }
        if (!$first) {
            return [[], []];
        }

        $months = $totals = [];
        foreach (CarbonPeriod::create($first, '1 month', Carbon::now($zone)->startOfMonth()) as $month) {
            $months[] = $month->format('F Y');
            $totals[] = round($byMonth[$month->format('Y-m')] ?? 0, 2);
        }

        return [$months, $totals];
    }

    // Percentage change; from nothing to something counts as +100%
    private function growth(float $current, float $previous): float
    {
        if ($previous > 0) {
            return round(($current - $previous) / $previous * 100, 2);
        }

        return $current > 0 ? 100 : 0;
    }
}
