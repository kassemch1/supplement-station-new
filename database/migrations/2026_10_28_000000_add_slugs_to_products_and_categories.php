<?php

use App\Support\Slugs;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Product and category URLs by name (/products/iso-100, /shop/creatine). Only adds columns and a
// table, filled from the existing names; rolling back removes them and leaves every other value as it was.
return new class extends Migration
{
    public function up(): void
    {
        foreach (['products' => 'product', 'categories' => 'category'] as $table => $fallback) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('slug', 100)->nullable()->after('name');
            });

            // Oldest first, so when two products share a name the older one keeps the plain URL
            foreach (DB::table($table)->orderBy('id')->get(['id', 'name']) as $row) {
                DB::table($table)->where('id', $row->id)->update([
                    'slug' => Slugs::unique($table, $row->name, $row->id, $fallback),
                ]);
            }

            Schema::table($table, function (Blueprint $table) {
                $table->unique('slug');
            });
        }

        // Slugs a product or category had before the admin renamed it: those links redirect to the new one
        Schema::create('old_slugs', function (Blueprint $table) {
            $table->id();
            $table->string('type', 30);
            $table->string('slug', 100);
            $table->unsignedBigInteger('target_id');
            $table->timestamp('created_at')->nullable();
            $table->unique(['type', 'slug']);
        });

        // The menu reads the categories from the cache (Category::cachedAll); reload them with the new column
        Cache::forget('store.categories');
    }

    public function down(): void
    {
        Schema::dropIfExists('old_slugs');

        foreach (['products', 'categories'] as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropUnique(['slug']);
                $table->dropColumn('slug');
            });
        }

        Cache::forget('store.categories');
    }
};
