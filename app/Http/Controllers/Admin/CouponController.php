<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $coupons = Coupon::query()
            ->when($request->filled('search'), function ($q) use ($request) {
                $term = '%' . $request->input('search') . '%';
                $q->where(fn ($q) => $q->where('code', 'like', $term)->orWhere('name', 'like', $term));
            })
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->input('type')))
            ->when($request->filled('status'), fn ($q) => $request->input('status') === 'active' ? $q->active() : $q->where('is_active', false))
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        return view('admin_views.manage_coupons.index', ['coupons' => $coupons]);
    }

    public function create()
    {
        return view('admin_views.manage_coupons.create');
    }

    public function store(CouponRequest $request)
    {
        $coupon = Coupon::create($request->couponData() + ['is_active' => true]);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon created successfully! Code: ' . $coupon->code);
    }

    public function edit(int $id)
    {
        return view('admin_views.manage_coupons.edit', ['coupon' => Coupon::findOrFail($id)]);
    }

    public function update(CouponRequest $request, int $id)
    {
        Coupon::findOrFail($id)->update($request->couponData() + ['is_active' => $request->has('is_active')]);

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon updated successfully!');
    }

    public function destroy(int $id)
    {
        $coupon = Coupon::findOrFail($id);

        // Orders keep a link to the coupon they used
        if ($coupon->orders()->exists()) {
            return redirect()->route('admin.coupons.index')
                ->with('error', 'Cannot delete coupon as it has been used in orders. You can deactivate it instead.');
        }

        $coupon->delete();

        return redirect()->route('admin.coupons.index')->with('success', 'Coupon deleted successfully!');
    }

    public function toggleStatus(int $id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon ' . ($coupon->is_active ? 'activated' : 'deactivated') . ' successfully!');
    }
}
