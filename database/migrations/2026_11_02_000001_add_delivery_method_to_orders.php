<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// orders.delivery_method: 'delivery' (brought to the customer's address, as every order so far) or 'pickup'
// (collected at the store; no address is asked, so the order's city and address are left empty).
// down() only removes the column.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('delivery_method', 20)->default('delivery')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('delivery_method');
        });
    }
};
