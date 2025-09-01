<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('coupon_id')->nullable()->after('status');
            $table->string('coupon_code')->nullable()->after('coupon_id');
            $table->decimal('coupon_discount_amount', 10, 2)->default(0)->after('coupon_code');
            $table->decimal('subtotal_amount', 10, 2)->after('coupon_discount_amount');
            
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['coupon_id']);
            $table->dropColumn(['coupon_id', 'coupon_code', 'coupon_discount_amount', 'subtotal_amount']);
        });
    }
};