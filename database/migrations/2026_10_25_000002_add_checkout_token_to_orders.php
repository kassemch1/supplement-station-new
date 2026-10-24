<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// One token per checkout page load; the unique index stops a double-click or retry from creating
// a second order. Existing orders keep NULL, which a unique index allows any number of times.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('orders', 'checkout_token')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('checkout_token', 64)->nullable()->unique()->after('status');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('orders', 'checkout_token')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropUnique(['checkout_token']);
                $table->dropColumn('checkout_token');
            });
        }
    }
};
