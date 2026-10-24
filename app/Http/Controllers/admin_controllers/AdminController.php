<?php

namespace App\Http\Controllers\admin_controllers;

use App\Http\Controllers\user_controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function loginView()
    {
        return view('admin_views/login');
    }

    public function admin_dashboard()
    {
        try {
            // Sales exclude cancelled orders. Dates are stored in UTC, while "today", "this week" and
            // "this month" are store-time (Beirut) periods converted to UTC for the query.
            $sales = fn () => DB::table('orders')->where('status', '!=', 'cancelled');
            $now = Carbon::now(config('app.display_timezone'));
            $period = fn (Carbon $from, Carbon $to) => [$from->copy()->utc(), $to->copy()->utc()];

            // Get monthly totals using Carbon for database compatibility
            $monthlyTotals = $sales()
                ->selectRaw('SUM(total_amount) as total_amount, EXTRACT(MONTH FROM created_at) as month, EXTRACT(YEAR FROM created_at) as year')
                ->whereNotNull('created_at')
                ->whereNotNull('total_amount')
                ->groupByRaw('EXTRACT(YEAR FROM created_at), EXTRACT(MONTH FROM created_at)')
                ->orderByRaw('EXTRACT(YEAR FROM created_at) ASC, EXTRACT(MONTH FROM created_at) ASC')
                ->get();

            // Prepare chart data
            $months = [];
            $totals = [];

            foreach ($monthlyTotals as $data) {
                if ($data->month && $data->total_amount !== null) {
                    $monthYear = Carbon::create($data->year, $data->month, 1)->format('F Y');
                    $months[] = $monthYear;
                    $totals[] = (float) $data->total_amount;
                }
            }

            // Get total amount for all time
            $totalAmount = $sales()->sum('total_amount') ?? 0;

            // Get THIS MONTH's income
            $thisMonthTotal = $sales()
                ->whereBetween('created_at', $period($now->copy()->startOfMonth(), $now->copy()->endOfMonth()))
                ->sum('total_amount') ?? 0;

            // Get LAST MONTH's income
            $lastMonth = $now->copy()->subMonthNoOverflow();
            $lastMonthTotal = $sales()
                ->whereBetween('created_at', $period($lastMonth->copy()->startOfMonth(), $lastMonth->copy()->endOfMonth()))
                ->sum('total_amount') ?? 0;

            // Calculate month-to-month comparison
            $monthlyGrowth = 0;
            $monthlyGrowthPercentage = 0;

            if ($lastMonthTotal > 0) {
                $monthlyGrowth = $thisMonthTotal - $lastMonthTotal;
                $monthlyGrowthPercentage = round((($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 2);
            } elseif ($thisMonthTotal > 0) {
                $monthlyGrowthPercentage = 100; // 100% growth if last month was 0
            }

            // Determine if growth is positive or negative
            $isGrowthPositive = $monthlyGrowth >= 0;

            // Order status counts
            $pendingCount = DB::table('orders')->where('status', 'pending')->count();
            $deliveredCount = DB::table('orders')->where('status', 'delivered')->count();
            $totalCount = DB::table('orders')->count();

            // Calculate percentages
            $pendingPercentage = $totalCount > 0 ? round(($pendingCount / $totalCount) * 100, 2) : 0;
            $deliveredPercentage = $totalCount > 0 ? round(($deliveredCount / $totalCount) * 100, 2) : 0;

            $today = $period($now->copy()->startOfDay(), $now->copy()->endOfDay());

            $todayOrders = $sales()
                ->whereBetween('created_at', $today)
                ->count();

            $todayRevenue = $sales()
                ->whereBetween('created_at', $today)
                ->sum('total_amount') ?? 0;

            // This week vs last week
            $thisWeekRevenue = $sales()
                ->whereBetween('created_at', $period($now->copy()->startOfWeek(), $now->copy()->endOfWeek()))
                ->sum('total_amount') ?? 0;

            $lastWeek = $now->copy()->subWeek();
            $lastWeekRevenue = $sales()
                ->whereBetween('created_at', $period($lastWeek->copy()->startOfWeek(), $lastWeek->copy()->endOfWeek()))
                ->sum('total_amount') ?? 0;

            // Weekly growth calculation
            $weeklyGrowth = $thisWeekRevenue - $lastWeekRevenue;
            $weeklyGrowthPercentage = $lastWeekRevenue > 0
                ? round((($thisWeekRevenue - $lastWeekRevenue) / $lastWeekRevenue) * 100, 2)
                : ($thisWeekRevenue > 0 ? 100 : 0);
            $isWeeklyGrowthPositive = $weeklyGrowth >= 0;
//            dd($lastWeekRevenue);
            return view('admin_views.admin_dashboard', compact(
                'months',
                'totals',
                'totalAmount',
                'thisMonthTotal',
                'lastMonthTotal',
                'monthlyGrowth',
                'monthlyGrowthPercentage',
                'isGrowthPositive',
                'pendingCount',
                'deliveredCount',
                'pendingPercentage',
                'deliveredPercentage',
                'todayOrders',
                'todayRevenue',
                'thisWeekRevenue',
                'lastWeekRevenue',
                'weeklyGrowthPercentage',
                'isWeeklyGrowthPositive',
            ));

        } catch (\Exception $e) {
            // Log the error and return with error message
            \Log::error('Dashboard error: ' . $e->getMessage());
            return view('admin_views.admin_dashboard')->with('error', 'Unable to load dashboard data');
        }
    }
    public function authenticate(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],

        ]);

        // Lock out password guessing: 5 failed attempts per email + IP, then wait
        $throttleKey = 'admin-login:'.Str::lower($request->input('email')).'|'.$request->ip();
        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $minutes = ceil(RateLimiter::availableIn($throttleKey) / 60);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$minutes} minute(s).",
            ])->onlyInput('email');
        }

        if (Auth::attempt($credentials)) {
            RateLimiter::clear($throttleKey);
            $request->session()->regenerate();

            return redirect()->intended('/manageProducts/create');
        }
        RateLimiter::hit($throttleKey, 15 * 60);
        Log::info('Login failed');
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }
    /********************************************************************* */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
