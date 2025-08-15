<?php

namespace App\Http\Controllers\admin_controllers;

use App\Http\Controllers\user_controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        $query = Coupon::query();

        // Search by code or name
        if ($request->has('search') && $request->input('search') != '') {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('code', 'LIKE', "%$searchTerm%")
                  ->orWhere('name', 'LIKE', "%$searchTerm%");
            });
        }

        // Filter by type
        if ($request->has('type') && $request->input('type') != '') {
            $query->where('type', $request->input('type'));
        }

        // Filter by status
        if ($request->has('status') && $request->input('status') != '') {
            if ($request->input('status') === 'active') {
                $query->active();
            } else {
                $query->where('is_active', false);
            }
        }

        $coupons = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('admin_views.manage_coupons.index', [
            'coupons' => $coupons,
        ]);
    }

    public function create()
    {
        return view('admin_views.manage_coupons.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:reusable,single_use',
            'discount_percentage' => 'required|numeric|between:0.01,100',
            'minimum_order_amount' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date|after:now',
        ], [
            'name.required' => 'The coupon name is required.',
            'type.required' => 'The coupon type is required.',
            'type.in' => 'The coupon type must be either reusable or single use.',
            'discount_percentage.required' => 'The discount percentage is required.',
            'discount_percentage.between' => 'The discount percentage must be between 0.01 and 100.',
            'discount_percentage.numeric' => 'The discount percentage must be a valid number.',
            'minimum_order_amount.required' => 'The minimum order amount is required.',
            'minimum_order_amount.min' => 'The minimum order amount must be at least 0.',
            'minimum_order_amount.numeric' => 'The minimum order amount must be a valid number.',
            'max_uses.integer' => 'The maximum uses must be a number.',
            'max_uses.min' => 'The maximum uses must be at least 1.',
            'expires_at.date' => 'The expiry date must be a valid date.',
            'expires_at.after' => 'The expiry date must be in the future.',
        ]);

        // Generate unique coupon code
        $code = $this->generateUniqueCode();

        // For single use coupons, ignore max_uses
        if ($validatedData['type'] === 'single_use') {
            $validatedData['max_uses'] = 1;
        }

        // Create the coupon
        $coupon = Coupon::create([
            'code' => $code,
            'name' => $validatedData['name'],
            'description' => $validatedData['description'],
            'type' => $validatedData['type'],
            'discount_percentage' => $validatedData['discount_percentage'],
            'minimum_order_amount' => $validatedData['minimum_order_amount'],
            'max_uses' => $validatedData['max_uses'],
            'expires_at' => $validatedData['expires_at'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon created successfully! Code: ' . $code);
    }

    public function edit($id)
    {
        $coupon = Coupon::findOrFail($id);
        return view('admin_views.manage_coupons.edit', [
            'coupon' => $coupon,
        ]);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:reusable,single_use',
            'discount_percentage' => 'required|numeric|between:0.01,100',
            'minimum_order_amount' => 'required|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'expires_at' => 'nullable|date',
            'is_active' => 'boolean',
        ], [
            'name.required' => 'The coupon name is required.',
            'type.required' => 'The coupon type is required.',
            'type.in' => 'The coupon type must be either reusable or single use.',
            'discount_percentage.required' => 'The discount percentage is required.',
            'discount_percentage.between' => 'The discount percentage must be between 0.01 and 100.',
            'minimum_order_amount.required' => 'The minimum order amount is required.',
            'minimum_order_amount.min' => 'The minimum order amount must be at least 0.',
            'max_uses.integer' => 'The maximum uses must be a number.',
            'max_uses.min' => 'The maximum uses must be at least 1.',
            'expires_at.date' => 'The expiry date must be a valid date.',
        ]);

        // Handle checkbox value
        $validatedData['is_active'] = $request->has('is_active');

        // For single use coupons, max_uses should be 1
        if ($validatedData['type'] === 'single_use') {
            $validatedData['max_uses'] = 1;
        }

        $coupon->update($validatedData);

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon updated successfully!');
    }

    public function destroy($id)
    {
        $coupon = Coupon::findOrFail($id);
        
        // Check if coupon has been used in any orders
        $orderCount = \App\Models\Order::where('coupon_id', $coupon->id)->count();
        
        if ($orderCount > 0) {
            return redirect()->route('admin.coupons.index')
                ->with('error', 'Cannot delete coupon as it has been used in orders. You can deactivate it instead.');
        }
        
        $coupon->delete();

        return redirect()->route('admin.coupons.index')
            ->with('success', 'Coupon deleted successfully!');
    }

    public function toggleStatus($id)
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->update(['is_active' => !$coupon->is_active]);

        $status = $coupon->is_active ? 'activated' : 'deactivated';
        return redirect()->route('admin.coupons.index')
            ->with('success', "Coupon {$status} successfully!");
    }

    /**
     * Generate a unique coupon code
     */
    private function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (Coupon::where('code', $code)->exists());

        return $code;
    }
}