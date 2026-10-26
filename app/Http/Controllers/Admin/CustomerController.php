<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ShippingDetail;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

// The store has no customer accounts: a customer is everyone who ordered with the same phone number
// (shipping_details.phone_key, the number in one form however it was typed). Spending and order counts
// leave out cancelled orders.
class CustomerController extends Controller
{
    // Customers who have not ordered for this many days count as "haven't come back"
    public const LAPSED_DAYS = 60;
    public const NEW_DAYS = 30;
    public const REGULAR_ORDERS = 3;

    public const SEGMENTS = [
        '' => 'All customers',
        'regulars' => 'Regulars (' . self::REGULAR_ORDERS . '+ orders)',
        'lapsed' => "Haven't come back (" . self::LAPSED_DAYS . '+ days)',
        'new' => 'New (first order in ' . self::NEW_DAYS . ' days)',
        'once' => 'One order only',
    ];

    public const SORTS = [
        'recent' => 'Last order: newest',
        'oldest' => 'Last order: longest ago',
        'orders' => 'Most orders',
        'spent' => 'Most spent',
    ];

    private const ORDERS = "SUM(CASE WHEN o.status != 'cancelled' THEN 1 ELSE 0 END)";
    // first and last real (not cancelled) order
    private const FIRST = "MIN(CASE WHEN o.status != 'cancelled' THEN o.created_at END)";
    private const LAST = "MAX(CASE WHEN o.status != 'cancelled' THEN o.created_at END)";

    public function index(Request $request)
    {
        $search = trim((string) $request->input('search'));
        $segment = array_key_exists($request->input('segment'), self::SEGMENTS) ? (string) $request->input('segment') : '';
        $sort = array_key_exists($request->input('sort'), self::SORTS) ? $request->input('sort') : 'recent';

        $customers = $this->grouped()
            ->when($search !== '', fn ($q) => $q->whereIn('s.phone_key', $this->matching($search)))
            ->when($segment === 'regulars', fn ($q) => $q->havingRaw(self::ORDERS . ' >= ?', [self::REGULAR_ORDERS]))
            ->when($segment === 'lapsed', fn ($q) => $q->havingRaw(self::ORDERS . ' >= 1')
                ->havingRaw(self::LAST . ' < ?', [now()->subDays(self::LAPSED_DAYS)->toDateTimeString()]))
            ->when($segment === 'new', fn ($q) => $q->havingRaw(self::FIRST . ' >= ?', [now()->subDays(self::NEW_DAYS)->toDateTimeString()]))
            ->when($segment === 'once', fn ($q) => $q->havingRaw(self::ORDERS . ' = 1'))
            ->orderByRaw(match ($sort) {
                // customers whose only orders were cancelled come last either way
                'oldest' => self::LAST . ' IS NULL, ' . self::LAST . ' ASC',
                'orders' => self::ORDERS . ' DESC, ' . self::LAST . ' DESC',
                'spent' => "SUM(CASE WHEN o.status != 'cancelled' THEN o.total_amount ELSE 0 END) DESC",
                default => self::LAST . ' IS NULL, ' . self::LAST . ' DESC',
            })
            ->paginate(20)
            ->withQueryString();

        // name, phone and city as given on each customer's latest order
        $latest = ShippingDetail::whereIn('phone_key', $customers->pluck('phone_key'))
            ->orderByDesc('order_id')->get(['phone_key', 'name', 'phone', 'city'])
            ->unique('phone_key')->keyBy('phone_key');

        return view('admin_views/customers/index', [
            'customers' => $customers,
            'latest' => $latest,
            'stats' => $this->stats(),
            'search' => $search,
            'segment' => $segment,
            'sort' => $sort,
        ]);
    }

    // One customer: everything they ordered, and the names, cities and emails they used
    public function show(string $phone)
    {
        $orders = Order::whereHas('shippingDetail', fn ($q) => $q->where('phone_key', $phone))
            ->with('shippingDetail')->withCount('orderItems')->latest('id')->get();
        abort_if($orders->isEmpty(), 404);

        $details = $orders->pluck('shippingDetail');
        $sales = $orders->where('status', '!=', 'cancelled');
        $emails = $details->pluck('email')->filter()->map(fn ($e) => strtolower(trim($e)))->unique()->values();

        return view('admin_views/customers/show', [
            'orders' => $orders,
            'customer' => [
                'name' => $details->first()->name,
                'phone' => $details->first()->phone,
                'phone_key' => $phone,
                'names' => $details->pluck('name')->filter()->unique()->values(),
                'cities' => $details->pluck('city')->filter()->unique()->values(),
                'emails' => $emails,
                'subscribed' => $emails->isNotEmpty() && Subscription::active()->whereIn('email', $emails)->exists(),
                'orders' => $sales->count(),
                'spent' => (float) $sales->sum('total_amount'),
                'average' => $sales->count() ? (float) $sales->avg('total_amount') : 0.0,
                // real orders; someone whose orders were all cancelled shows those dates
                'first' => ($sales->last() ?? $orders->last())->created_at,
                'last' => ($sales->first() ?? $orders->first())->created_at,
                'cancelled' => $orders->where('status', 'cancelled')->count(),
            ],
        ]);
    }

    // One row per phone number with its totals
    private function grouped()
    {
        return DB::table('shipping_details as s')
            ->join('orders as o', 'o.id', '=', 's.order_id')
            ->whereNotNull('s.phone_key')
            ->groupBy('s.phone_key')
            ->selectRaw('s.phone_key, ' . self::ORDERS . " as orders_count,
                SUM(CASE WHEN o.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
                SUM(CASE WHEN o.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                SUM(CASE WHEN o.status != 'cancelled' THEN o.total_amount ELSE 0 END) as spent,
                " . self::FIRST . " as first_at, " . self::LAST . " as last_at");
    }

    // Phone numbers of the customers a search finds: digits are looked for in the phone, words in the name
    private function matching(string $search)
    {
        $digits = preg_replace('/\D+/', '', $search);

        return DB::table('shipping_details')->select('phone_key')->whereNotNull('phone_key')
            ->where(function ($where) use ($search, $digits) {
                $where->whereRaw('1 = 0');
                if (strlen($digits) >= 3) {
                    $where->orWhere('phone_key', 'like', '%' . (strlen($digits) >= 7 ? ShippingDetail::phoneKey($digits) : $digits) . '%');
                }
                if (preg_match('/\pL/u', $search)) {
                    $where->orWhere('name', 'like', '%' . addcslashes($search, '%_\\') . '%');
                }
            });
    }

    // The number cards at the top (all customers, whatever the filter)
    private function stats(): array
    {
        $row = DB::query()->fromSub($this->grouped(), 'c')->selectRaw('COUNT(*) as total,
            SUM(CASE WHEN orders_count >= 2 THEN 1 ELSE 0 END) as came_back,
            SUM(CASE WHEN orders_count >= ? THEN 1 ELSE 0 END) as regulars,
            SUM(CASE WHEN orders_count >= 1 AND last_at < ? THEN 1 ELSE 0 END) as lapsed,
            SUM(CASE WHEN first_at >= ? THEN 1 ELSE 0 END) as fresh,
            SUM(spent) as spent', [self::REGULAR_ORDERS, now()->subDays(self::LAPSED_DAYS)->toDateTimeString(), now()->subDays(self::NEW_DAYS)->toDateTimeString()])
            ->first();

        $total = (int) ($row->total ?? 0);

        return [
            'total' => $total,
            'returning' => (int) ($row->came_back ?? 0),
            'returningShare' => $total ? round(($row->came_back ?? 0) / $total * 100) : 0,
            'regulars' => (int) ($row->regulars ?? 0),
            'lapsed' => (int) ($row->lapsed ?? 0),
            'new' => (int) ($row->fresh ?? 0),
            'average' => $total ? (float) $row->spent / $total : 0.0,
        ];
    }
}
