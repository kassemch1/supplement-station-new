<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// Sales exclude cancelled orders. Dates are stored in UTC, while "today", "this week", the chart's
// buckets and the filter's days are store-time (Beirut) periods.
class DashboardController extends Controller
{
    // The filter buttons: ?range=<key>, or ?range=custom&from=Y-m-d&to=Y-m-d
    public const RANGES = [
        'today' => 'Today',
        '7d' => '7 days',
        '30d' => '30 days',
        '90d' => '90 days',
        'this_month' => 'This month',
        'last_month' => 'Last month',
        'this_year' => 'This year',
        'all' => 'All time',
    ];

    public function index(Request $request)
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

        // Every all-time number in one pass over the orders
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
            'report' => $this->report($request, $zone, $now),
            'recentOrders' => Order::with('shippingDetail')->latest('id')->limit(8)->get(),
            'ranges' => self::RANGES,
            'soldOut' => $this->soldOut(),
        ]);
    }

    // The sold-out card: products out of stock (not archived), the ones that sold most in the last 90 days
    // first, then the ones sold out longest. Two queries whatever the number of products.
    private function soldOut(int $show = 6): array
    {
        $products = Product::where('stock', 0)->with('images')->get(['id', 'name', 'slug', 'stock', 'sold_out_at', 'updated_at']);
        $sold = $products->isEmpty() ? collect() : DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('order_items.product_id', $products->modelKeys())
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.created_at', '>=', now()->subDays(90))
            ->groupBy('order_items.product_id')
            ->selectRaw('order_items.product_id, SUM(order_items.quantity) as qty')
            ->pluck('qty', 'product_id');

        $products->each(fn (Product $p) => $p->setAttribute('sold_recently', (int) ($sold[$p->id] ?? 0)));
        $sorted = $products->sort(fn (Product $a, Product $b) => [$b->sold_recently, $a->sold_out_at ?? $a->updated_at] <=> [$a->sold_recently, $b->sold_out_at ?? $b->updated_at]);

        return ['count' => $products->count(), 'items' => $sorted->take($show)->values()];
    }

    // Everything the filtered part of the dashboard shows, for the chosen period and the one before it
    private function report(Request $request, string $zone, Carbon $now): array
    {
        [$range, $from, $to, $label] = $this->period($request, $zone, $now);
        $days = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;
        $prevTo = $from->copy()->subSecond();
        $prevFrom = $range === 'all' ? null : $prevTo->copy()->subDays($days - 1)->startOfDay();

        // bucket size: hours for one day, days up to three months, months after that
        $unit = $days <= 1 ? 'hour' : ($days <= 92 ? 'day' : 'month');
        $keyOf = fn (Carbon $t) => $t->format(['hour' => 'Y-m-d H', 'day' => 'Y-m-d', 'month' => 'Y-m'][$unit]);
        $buckets = [];
        $cursor = $unit === 'month' ? $from->copy()->startOfMonth() : ($unit === 'day' ? $from->copy()->startOfDay() : $from->copy()->startOfHour());
        while ($cursor <= $to) {
            $buckets[$keyOf($cursor)] = [
                'label' => $cursor->format(['hour' => 'H:00', 'day' => 'M j', 'month' => 'M Y'][$unit]),
                'revenue' => 0.0, 'orders' => 0, 'items' => 0,
            ];
            $unit === 'month' ? $cursor->addMonthNoOverflow() : ($unit === 'day' ? $cursor->addDay() : $cursor->addHour());
        }

        $cur = ['revenue' => 0.0, 'orders' => 0, 'items' => 0];
        $prev = $cur;
        $status = ['pending' => 0, 'delivered' => 0, 'cancelled' => 0];
        $weekday = array_fill(0, 7, 0);    // Monday first
        $hour = array_fill(0, 24, 0);
        $cities = [];

        $scanFrom = ($prevFrom ?? $from)->copy()->utc();
        $orders = DB::table('orders')
            ->leftJoin('shipping_details', 'shipping_details.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$scanFrom, $to->copy()->utc()])
            ->select(['orders.id', 'orders.created_at', 'orders.total_amount', 'orders.status', 'shipping_details.city'])
            ->orderBy('orders.id')
            ->cursor();
        $seen = [];
        foreach ($orders as $order) {
            if (isset($seen[$order->id])) {
                continue;                      // an order with two shipping rows counts once
            }
            $seen[$order->id] = true;
            $at = Carbon::parse($order->created_at, 'UTC')->setTimezone($zone);
            $inRange = $at >= $from;
            if ($inRange && isset($status[$order->status])) {
                $status[$order->status]++;
            }
            if ($order->status === 'cancelled') {
                continue;
            }
            $amount = (float) $order->total_amount;
            if (!$inRange) {
                $prev['revenue'] += $amount;
                $prev['orders']++;
                continue;
            }
            $cur['revenue'] += $amount;
            $cur['orders']++;
            $key = $keyOf($at);
            if (isset($buckets[$key])) {
                $buckets[$key]['revenue'] += $amount;
                $buckets[$key]['orders']++;
            }
            $weekday[$at->dayOfWeekIso - 1]++;
            $hour[$at->hour]++;
            $city = trim((string) $order->city);
            $cityKey = mb_strtolower($city === '' ? '—' : $city);
            $cities[$cityKey] ??= ['name' => $city === '' ? 'Not given' : $city, 'orders' => 0, 'revenue' => 0.0];
            $cities[$cityKey]['orders']++;
            $cities[$cityKey]['revenue'] += $amount;
        }

        // Items and best sellers from the order lines of the same orders
        $products = [];
        $lines = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', '!=', 'cancelled')
            ->whereBetween('orders.created_at', [$scanFrom, $to->copy()->utc()])
            ->select(['orders.created_at', 'order_items.product_id', 'order_items.product_name', 'order_items.quantity', 'order_items.price'])
            ->cursor();
        foreach ($lines as $line) {
            $at = Carbon::parse($line->created_at, 'UTC')->setTimezone($zone);
            $qty = (int) $line->quantity;
            if ($at < $from) {
                $prev['items'] += $qty;
                continue;
            }
            $cur['items'] += $qty;
            $key = $keyOf($at);
            if (isset($buckets[$key])) {
                $buckets[$key]['items'] += $qty;
            }
            $name = trim((string) $line->product_name) ?: 'Product #' . $line->product_id;
            $productKey = mb_strtolower($name);
            $products[$productKey] ??= ['name' => $name, 'qty' => 0, 'revenue' => 0.0];
            $products[$productKey]['qty'] += $qty;
            $products[$productKey]['revenue'] += $qty * (float) $line->price;
        }

        uasort($products, fn ($a, $b) => [$b['revenue'], $b['qty']] <=> [$a['revenue'], $a['qty']]);
        uasort($cities, fn ($a, $b) => [$b['orders'], $b['revenue']] <=> [$a['orders'], $a['revenue']]);
        $avg = fn (array $p) => $p['orders'] > 0 ? $p['revenue'] / $p['orders'] : 0.0;
        $delta = fn (float $a, float $b) => $prevFrom ? $this->growth($a, $b) : null;

        return [
            'range' => $range,
            'label' => $label,
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'compare' => $prevFrom ? $prevFrom->format('M j, Y') . ' – ' . $prevTo->format('M j, Y') : null,
            'unit' => $unit,
            'kpis' => [
                'revenue' => ['value' => round($cur['revenue'], 2), 'prev' => round($prev['revenue'], 2), 'delta' => $delta($cur['revenue'], $prev['revenue'])],
                'orders' => ['value' => $cur['orders'], 'prev' => $prev['orders'], 'delta' => $delta($cur['orders'], $prev['orders'])],
                'average' => ['value' => round($avg($cur), 2), 'prev' => round($avg($prev), 2), 'delta' => $delta($avg($cur), $avg($prev))],
                'items' => ['value' => $cur['items'], 'prev' => $prev['items'], 'delta' => $delta($cur['items'], $prev['items'])],
            ],
            'series' => [
                'labels' => array_column($buckets, 'label'),
                'revenue' => array_map(fn ($b) => round($b['revenue'], 2), array_values($buckets)),
                'orders' => array_column($buckets, 'orders'),
                'items' => array_column($buckets, 'items'),
            ],
            'status' => $status,
            'weekday' => $weekday,
            'hour' => $hour,
            'products' => array_slice(array_values($products), 0, 6),
            'cities' => array_slice(array_values($cities), 0, 6),
        ];
    }

    // [range key, from, to (store time), label]; anything unknown falls back to the last 30 days
    private function period(Request $request, string $zone, Carbon $now): array
    {
        $range = $request->query('range', '30d');
        $today = $now->copy()->endOfDay();

        if ($range === 'custom') {
            try {
                $from = Carbon::createFromFormat('Y-m-d', (string) $request->query('from'), $zone)->startOfDay();
                $to = Carbon::createFromFormat('Y-m-d', (string) $request->query('to'), $zone)->endOfDay();
                if ($from > $to) {
                    [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
                }
                if ($from->diffInDays($to) > 3660) {
                    $from = $to->copy()->subYears(10)->startOfDay();
                }

                return ['custom', $from, $to, $from->format('M j, Y') . ' – ' . $to->format('M j, Y')];
            } catch (\Throwable) {
                $range = '30d';
            }
        }

        $lastMonth = $now->copy()->subMonthNoOverflow();
        [$from, $to] = match ($range) {
            'today' => [$now->copy()->startOfDay(), $today],
            '7d' => [$now->copy()->subDays(6)->startOfDay(), $today],
            '90d' => [$now->copy()->subDays(89)->startOfDay(), $today],
            'this_month' => [$now->copy()->startOfMonth(), $today],
            'last_month' => [$lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth()],
            'this_year' => [$now->copy()->startOfYear(), $today],
            'all' => [$this->firstOrderDay($zone) ?? $now->copy()->startOfDay(), $today],
            default => [$now->copy()->subDays(29)->startOfDay(), $today],
        };
        $range = array_key_exists($range, self::RANGES) ? $range : '30d';

        return [$range, $from, $to, self::RANGES[$range]];
    }

    private function firstOrderDay(string $zone): ?Carbon
    {
        $first = DB::table('orders')->min('created_at');

        return $first ? Carbon::parse($first, 'UTC')->setTimezone($zone)->startOfDay() : null;
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
