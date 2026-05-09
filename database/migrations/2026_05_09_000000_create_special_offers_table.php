<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('special_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('special_offers');
    }
};
