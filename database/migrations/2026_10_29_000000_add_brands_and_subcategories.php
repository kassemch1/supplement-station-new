<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

// Brands, subcategories (a category inside another one), category icons and pictures for option values
// (a flavour, a size, a colour). Only adds a table and columns; rolling back removes exactly those.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('slug', 100)->unique();
            $table->string('logo')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('categories', function (Blueprint $table) {
            // A subcategory points at its main category. Deleting a main category keeps its subcategories
            // (they become main categories), never their products.
            $table->foreignId('parent_id')->nullable()->after('slug')->constrained('categories')->nullOnDelete();
            $table->string('icon', 40)->nullable()->after('parent_id');
            $table->string('image')->nullable()->after('icon');
            $table->unsignedInteger('sort_order')->default(0)->after('image');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('brand_id')->nullable()->after('category_id')->constrained('brands')->nullOnDelete();
        });

        Schema::table('product_options', function (Blueprint $table) {
            $table->string('image')->nullable()->after('option_value');
        });

        Cache::forget('store.categories');
    }

    public function down(): void
    {
        Schema::table('product_options', function (Blueprint $table) {
            $table->dropColumn('image');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('brand_id');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropColumn(['icon', 'image', 'sort_order']);
        });

        Schema::dropIfExists('brands');

        Cache::forget('store.categories');
    }
};
