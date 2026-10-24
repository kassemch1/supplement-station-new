<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Orders placed before coupons existed (before Sept 2025) got subtotal_amount = 0 when the column was
// added. None of them used a coupon, so their subtotal is simply their total.
return new class extends Migration
{
    private const BEFORE_COUPONS = '2025-09-01 00:00:00';

    public function up(): void
    {
        DB::table('orders')
            ->where('subtotal_amount', 0)
            ->whereNull('coupon_id')
            ->where('created_at', '<', self::BEFORE_COUPONS)
            ->update(['subtotal_amount' => DB::raw('total_amount')]);
    }

    public function down(): void
    {
        DB::table('orders')
            ->whereNull('coupon_id')
            ->where('created_at', '<', self::BEFORE_COUPONS)
            ->whereColumn('subtotal_amount', 'total_amount')
            ->update(['subtotal_amount' => 0]);
    }
};
