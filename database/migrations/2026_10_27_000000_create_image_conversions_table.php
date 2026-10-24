<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Log of `php artisan images:convert-webp`: which row got which WebP file instead of which original.
// The originals stay on disk, so every conversion can be undone (images:convert-webp --revert, or
// rolling back this migration, which puts the old paths back first).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('image_conversions', function (Blueprint $table) {
            $table->id();
            $table->string('table_name', 50);
            $table->unsignedBigInteger('row_id');
            $table->string('old_url', 191);
            $table->string('new_url', 191);
            $table->timestamps();
            $table->index(['table_name', 'row_id']);
        });
    }

    public function down(): void
    {
        $columns = ['product_images' => 'url', 'banners' => 'image'];

        foreach (DB::table('image_conversions')->get() as $conversion) {
            $column = $columns[$conversion->table_name] ?? null;
            if ($column) {
                DB::table($conversion->table_name)
                    ->where('id', $conversion->row_id)
                    ->where($column, $conversion->new_url)
                    ->update([$column => $conversion->old_url]);
            }
        }

        Schema::dropIfExists('image_conversions');
    }
};
