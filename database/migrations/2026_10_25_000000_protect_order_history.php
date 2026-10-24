<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Deleting a product used to cascade-delete every order line that contained it (a quarter of all
// orders lost their items). From now on products are archived (soft deleted), order lines keep a
// copy of the product name and image, and removing a product row can never delete an order line.
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'deleted_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->softDeletes()->index();
            });
        }

        if (!Schema::hasColumn('order_items', 'product_name')) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->string('product_name')->nullable()->after('product_id');
                $table->string('product_image')->nullable()->after('product_name');
            });
        }

        // Snapshot the current product name and first image into every existing order line
        DB::table('order_items')->whereNull('product_name')->orderBy('id')->chunkById(500, function ($items) {
            $products = DB::table('products')->whereIn('id', $items->pluck('product_id'))->pluck('name', 'id');
            $images = DB::table('product_images')->whereIn('product_id', $items->pluck('product_id'))
                ->orderBy('id')->get()->groupBy('product_id')->map(fn ($g) => $g->first()->url);

            foreach ($items as $item) {
                if (isset($products[$item->product_id])) {
                    DB::table('order_items')->where('id', $item->id)->update([
                        'product_name' => $products[$item->product_id],
                        'product_image' => $images[$item->product_id] ?? null,
                    ]);
                }
            }
        });

        // order_items.product_id: ON DELETE CASCADE -> nullable, ON DELETE SET NULL
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });
    }

    public function down(): void
    {
        // Restores the original schema exactly when every order line still has its product (the normal
        // case right after a deploy). If some product row was removed meanwhile, those lines keep
        // product_id = NULL and their saved name/image columns instead of being lost.
        $allLinesHaveProducts = DB::table('order_items')->whereNull('product_id')->doesntExist();

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
        });
        Schema::table('order_items', function (Blueprint $table) use ($allLinesHaveProducts) {
            if ($allLinesHaveProducts) {
                $table->unsignedBigInteger('product_id')->nullable(false)->change();
            }
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });

        if ($allLinesHaveProducts) {
            Schema::table('order_items', function (Blueprint $table) {
                $table->dropColumn(['product_name', 'product_image']);
            });
        }

        if (DB::table('products')->whereNotNull('deleted_at')->doesntExist()) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }
};
