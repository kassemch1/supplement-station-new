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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['reusable', 'single_use'])->default('reusable');
            $table->decimal('discount_percentage', 5, 2); // 0.00 to 100.00
            $table->decimal('minimum_order_amount', 10, 2)->default(0); // Minimum order amount to apply coupon
            $table->integer('max_uses')->nullable(); // For reusable coupons, null means unlimited
            $table->integer('used_count')->default(0); // Track how many times used
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable(); // Optional expiry date
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
}; 