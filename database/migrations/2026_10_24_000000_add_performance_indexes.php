<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Additive only: adds indexes for the lookups and sorts the site runs most, no data is changed.
return new class extends Migration
{
    private array $indexes = [
        // Admin order lists filter by status and sort by date; the dashboard and best sellers filter by status/date
        'orders' => [['status', 'created_at'], ['created_at']],
        // Shop default sort, and category filter + sort
        'products' => [['created_at'], ['category_id', 'created_at']],
        // carts:cleanup deletes by expiry
        'carts' => [['expires_at']],
        // Admin subscribers list sorts by date
        'subscriptions' => [['created_at']],
    ];

    public function up(): void
    {
        // Every cart request looks the cart up by session_id. Make it unique so one session can't end up
        // with two carts, unless live data already has duplicates, in which case use a plain index.
        if (!Schema::hasIndex('carts', ['session_id'])) {
            $hasDuplicates = DB::table('carts')->select('session_id')
                ->groupBy('session_id')->havingRaw('count(*) > 1')->exists();

            Schema::table('carts', function (Blueprint $table) use ($hasDuplicates) {
                $hasDuplicates ? $table->index('session_id') : $table->unique('session_id');
            });
        }

        foreach ($this->indexes as $tableName => $indexes) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexes) {
                foreach ($indexes as $columns) {
                    if (!Schema::hasIndex($tableName, $columns)) {
                        $table->index($columns);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('carts', function (Blueprint $table) {
            if (Schema::hasIndex('carts', ['session_id'], 'unique')) {
                $table->dropUnique(['session_id']);
            } elseif (Schema::hasIndex('carts', ['session_id'])) {
                $table->dropIndex(['session_id']);
            }
        });

        foreach ($this->indexes as $tableName => $indexes) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName, $indexes) {
                foreach ($indexes as $columns) {
                    if (Schema::hasIndex($tableName, $columns)) {
                        $table->dropIndex($columns);
                    }
                }
            });
        }
    }
};
