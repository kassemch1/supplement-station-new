<?php

namespace App\Http\Controllers\admin_controllers;

use App\Http\Controllers\user_controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function loginView()
    {
        return view('admin_views/login');
    }

    public function admin_dashboard()
    {
        try {
            // Get monthly totals using Carbon for database compatibility
            $monthlyTotals = DB::table('orders')
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
            $totalAmount = DB::table('orders')->sum('total_amount') ?? 0;

            // Get THIS MONTH's income
            $thisMonthTotal = DB::table('orders')
                ->whereBetween('created_at', [
                    Carbon::now()->startOfMonth(),
                    Carbon::now()->endOfMonth()
                ])
                ->sum('total_amount') ?? 0;

            // Get LAST MONTH's income
            $lastMonthTotal = DB::table('orders')
                ->whereBetween('created_at', [
                    Carbon::now()->subMonth()->startOfMonth(),
                    Carbon::now()->subMonth()->endOfMonth()
                ])
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


            $todayOrders = DB::table('orders')
                ->whereDate('created_at', Carbon::today())
                ->count();

            $todayRevenue = DB::table('orders')
                ->whereDate('created_at', Carbon::today())
                ->sum('total_amount') ?? 0;

            // This week vs last week
            $thisWeekRevenue = DB::table('orders')
                ->whereBetween('created_at', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])
                ->sum('total_amount') ?? 0;

            $lastWeekRevenue = DB::table('orders')
                ->whereBetween('created_at', [
                    Carbon::now()->subWeek()->startOfWeek(),
                    Carbon::now()->subWeek()->endOfWeek()
                ])
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

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            return redirect()->intended('/manageProducts/create');
        }
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
