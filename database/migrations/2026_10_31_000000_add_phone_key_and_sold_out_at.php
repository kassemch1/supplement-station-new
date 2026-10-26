<?php

use App\Models\ShippingDetail;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// shipping_details.phone_key: the order's phone as plain digits in one form ("+961 3 123 456", "03 123 456"
// and "03123456" all become 03123456), for the admin's order search and the Customers page.
// products.sold_out_at: when a product went out of stock, for the dashboard's sold-out card; the products
// already sold out get their last-changed time. down() only removes the two columns (nothing else changes).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_details', function (Blueprint $table) {
            $table->string('phone_key', 20)->nullable()->after('phone')->index();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->timestamp('sold_out_at')->nullable()->after('stock');
        });

        DB::table('shipping_details')->select('id', 'phone')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                DB::table('shipping_details')->where('id', $row->id)->update(['phone_key' => ShippingDetail::phoneKey($row->phone)]);
            }
        });
        DB::table('products')->where('stock', 0)->update(['sold_out_at' => DB::raw('updated_at')]);
    }

    public function down(): void
    {
        Schema::table('shipping_details', function (Blueprint $table) {
            $table->dropIndex(['phone_key']);
            $table->dropColumn('phone_key');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('sold_out_at');
        });
    }
};
