<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

// Restores order lines that were cascade-deleted along with their products. Recovered from the
// May 2025 database backup (backups/db-backup.sql): 37 lines across 34 orders. 33 of those orders
// add up exactly to their stored total; order #24 is $5 short, as it already was in that backup.
// Only orders that exist and currently have no lines are touched, so this is safe to re-run.
return new class extends Migration
{
    private array $lines = [
        ['order_id' => 12, 'product_id' => 86, 'product_name' => 'Bcaa + Creatine', 'product_image' => 'storage/products_images/PwOrrh159B6UxFKTqat0rSrB1e12lRyIctE32Cjn.png', 'quantity' => 1, 'price' => '30.00', 'selected_options' => '[]', 'created_at' => '2024-09-09 02:16:40', 'updated_at' => '2024-09-09 02:16:40'],
        ['order_id' => 16, 'product_id' => 13, 'product_name' => '2 gold creatine + shaker', 'product_image' => 'storage/products_images/0ePi65kVJ35xXEvOsgIgdslbTSz1gkDm8EB5e6Nd.jpg', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '[]', 'created_at' => '2024-09-14 16:56:48', 'updated_at' => '2024-09-14 16:56:48'],
        ['order_id' => 18, 'product_id' => 13, 'product_name' => '2 gold creatine + shaker', 'product_image' => 'storage/products_images/0ePi65kVJ35xXEvOsgIgdslbTSz1gkDm8EB5e6Nd.jpg', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '[]', 'created_at' => '2024-09-16 02:57:38', 'updated_at' => '2024-09-16 02:57:38'],
        ['order_id' => 21, 'product_id' => 13, 'product_name' => '2 gold creatine + shaker', 'product_image' => 'storage/products_images/0ePi65kVJ35xXEvOsgIgdslbTSz1gkDm8EB5e6Nd.jpg', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '[]', 'created_at' => '2024-09-19 00:39:46', 'updated_at' => '2024-09-19 00:39:46'],
        ['order_id' => 23, 'product_id' => 162, 'product_name' => 'RULE 1 CITRULLINE (60 SERVINGS)', 'product_image' => 'storage/products_images/WcDphVynUgki52p1rT8wwaQXeNURGRsDyiWQeP95.png', 'quantity' => 1, 'price' => '25.00', 'selected_options' => '[]', 'created_at' => '2024-09-21 18:31:47', 'updated_at' => '2024-09-21 18:31:47'],
        ['order_id' => 23, 'product_id' => 15, 'product_name' => '2 nitrotech', 'product_image' => 'storage/products_images/huQPdz6ZFFvUnPhEHIiHQXWlEU8RTaGfHZffvJAw.jpg', 'quantity' => 1, 'price' => '99.00', 'selected_options' => '{"Flavor":"Chocolate"}', 'created_at' => '2024-09-21 18:31:47', 'updated_at' => '2024-09-21 18:31:47'],
        ['order_id' => 24, 'product_id' => 5, 'product_name' => 'Nitro Tech whey gold (907g)', 'product_image' => 'storage/products_images/ukkCdOUQnpqAholXyv648lFk4OVP49pt2kfyvedw.png', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '{"Flavor":"Cookies & Cream"}', 'created_at' => '2024-09-29 03:05:31', 'updated_at' => '2024-09-29 03:05:31'],
        ['order_id' => 26, 'product_id' => 79, 'product_name' => 'RED REX EAA + CREATINE HCL (30 SERVINGS)', 'product_image' => 'storage/products_images/Iv1wATp29HwTYddUVi3T8Ammqw1JtHGTX9E6rYVg.png', 'quantity' => 1, 'price' => '30.00', 'selected_options' => '{"Flavor":"Mango"}', 'created_at' => '2024-09-30 00:44:27', 'updated_at' => '2024-09-30 00:44:27'],
        ['order_id' => 28, 'product_id' => 13, 'product_name' => '2 gold creatine + shaker', 'product_image' => 'storage/products_images/0ePi65kVJ35xXEvOsgIgdslbTSz1gkDm8EB5e6Nd.jpg', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '[]', 'created_at' => '2024-10-03 21:08:01', 'updated_at' => '2024-10-03 21:08:01'],
        ['order_id' => 30, 'product_id' => 13, 'product_name' => '2 gold creatine + shaker', 'product_image' => 'storage/products_images/0ePi65kVJ35xXEvOsgIgdslbTSz1gkDm8EB5e6Nd.jpg', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '[]', 'created_at' => '2024-10-05 04:58:46', 'updated_at' => '2024-10-05 04:58:46'],
        ['order_id' => 31, 'product_id' => 30, 'product_name' => 'LEVRONE GOLD CREATINE (300g)', 'product_image' => 'storage/products_images/yoawB4WbeFFvOU6Jur3SXYM5JgbpV5pCsiDTzIPI.png', 'quantity' => 1, 'price' => '18.40', 'selected_options' => '[]', 'created_at' => '2024-10-12 21:51:04', 'updated_at' => '2024-10-12 21:51:04'],
        ['order_id' => 32, 'product_id' => 30, 'product_name' => 'LEVRONE GOLD CREATINE (300g)', 'product_image' => 'storage/products_images/yoawB4WbeFFvOU6Jur3SXYM5JgbpV5pCsiDTzIPI.png', 'quantity' => 1, 'price' => '18.40', 'selected_options' => '[]', 'created_at' => '2024-10-12 22:10:17', 'updated_at' => '2024-10-12 22:10:17'],
        ['order_id' => 42, 'product_id' => 13, 'product_name' => '2 gold creatine + shaker', 'product_image' => 'storage/products_images/0ePi65kVJ35xXEvOsgIgdslbTSz1gkDm8EB5e6Nd.jpg', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '[]', 'created_at' => '2024-10-13 19:11:03', 'updated_at' => '2024-10-13 19:11:03'],
        ['order_id' => 50, 'product_id' => 30, 'product_name' => 'LEVRONE GOLD CREATINE (300g)', 'product_image' => 'storage/products_images/yoawB4WbeFFvOU6Jur3SXYM5JgbpV5pCsiDTzIPI.png', 'quantity' => 1, 'price' => '18.40', 'selected_options' => '[]', 'created_at' => '2024-10-20 20:40:48', 'updated_at' => '2024-10-20 20:40:48'],
        ['order_id' => 61, 'product_id' => 61, 'product_name' => 'MUSCLETECH 100% FISH OIL', 'product_image' => 'storage/products_images/HrPEz33xatcquOLZ9ifZ7Qqb8EXKjtOj2B7ILfa9.png', 'quantity' => 1, 'price' => '15.00', 'selected_options' => '[]', 'created_at' => '2024-11-05 17:51:04', 'updated_at' => '2024-11-05 17:51:04'],
        ['order_id' => 70, 'product_id' => 13, 'product_name' => '2 gold creatine + shaker', 'product_image' => 'storage/products_images/0ePi65kVJ35xXEvOsgIgdslbTSz1gkDm8EB5e6Nd.jpg', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '[]', 'created_at' => '2024-11-23 00:32:58', 'updated_at' => '2024-11-23 00:32:58'],
        ['order_id' => 71, 'product_id' => 13, 'product_name' => '2 gold creatine + shaker', 'product_image' => 'storage/products_images/0ePi65kVJ35xXEvOsgIgdslbTSz1gkDm8EB5e6Nd.jpg', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '[]', 'created_at' => '2024-11-23 06:37:48', 'updated_at' => '2024-11-23 06:37:48'],
        ['order_id' => 77, 'product_id' => 95, 'product_name' => 'ASHWAGANDHA NOW (90 VEG CAPSULES)', 'product_image' => 'storage/products_images/ZN9z7dvXb2VUnapG66e71EAagDD66dQPzarvqKn6.png', 'quantity' => 1, 'price' => '25.00', 'selected_options' => '[]', 'created_at' => '2024-12-20 09:00:19', 'updated_at' => '2024-12-20 09:00:19'],
        ['order_id' => 81, 'product_id' => 44, 'product_name' => 'Inner Armour ANABOLIC PEAK Gainer (2.7KG)', 'product_image' => 'storage/products_images/fljj3pmWceVvg5rOCwzpquH803fhKtbXiY1cLB1o.png', 'quantity' => 2, 'price' => '35.00', 'selected_options' => '{"Flavor":"Chocolate"}', 'created_at' => '2025-01-09 02:31:03', 'updated_at' => '2025-01-09 02:31:03'],
        ['order_id' => 82, 'product_id' => 13, 'product_name' => '2 gold creatine + shaker', 'product_image' => 'storage/products_images/0ePi65kVJ35xXEvOsgIgdslbTSz1gkDm8EB5e6Nd.jpg', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '[]', 'created_at' => '2025-01-11 09:01:34', 'updated_at' => '2025-01-11 09:01:34'],
        ['order_id' => 83, 'product_id' => 138, 'product_name' => 'COLLAGEN PEPTIDES (20 SERVINGS)', 'product_image' => 'storage/products_images/FXE8bQQyiYUbhHbod5Tr7KqjELurWYSDWuM4WYjw.png', 'quantity' => 2, 'price' => '25.00', 'selected_options' => '{"Flavor":"Chocolate"}', 'created_at' => '2025-01-12 18:13:58', 'updated_at' => '2025-01-12 18:13:58'],
        ['order_id' => 84, 'product_id' => 96, 'product_name' => 'ASHWAGANDHA ROOT 920 MG (90 CAPSULES)', 'product_image' => 'storage/products_images/SNKaCeNOBImXD8SiOuFMkjV1ctOyzQgg8gif70DJ.png', 'quantity' => 2, 'price' => '25.00', 'selected_options' => '[]', 'created_at' => '2025-01-16 03:39:05', 'updated_at' => '2025-01-16 03:39:05'],
        ['order_id' => 85, 'product_id' => 5, 'product_name' => 'Nitro Tech whey gold (907g)', 'product_image' => 'storage/products_images/ukkCdOUQnpqAholXyv648lFk4OVP49pt2kfyvedw.png', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '{"Flavor":"Cookies & Cream"}', 'created_at' => '2025-01-20 18:08:40', 'updated_at' => '2025-01-20 18:08:40'],
        ['order_id' => 87, 'product_id' => 30, 'product_name' => 'LEVRONE GOLD CREATINE (300g)', 'product_image' => 'storage/products_images/yoawB4WbeFFvOU6Jur3SXYM5JgbpV5pCsiDTzIPI.png', 'quantity' => 1, 'price' => '18.40', 'selected_options' => '[]', 'created_at' => '2025-01-23 00:21:02', 'updated_at' => '2025-01-23 00:21:02'],
        ['order_id' => 89, 'product_id' => 114, 'product_name' => 'INNER ARMOUR CREATINE (133 SERVINGS)', 'product_image' => 'storage/products_images/LHDO0qq5duqPBRL3J1WHyH45QkLkEPc7WvussMwT.png', 'quantity' => 1, 'price' => '20.00', 'selected_options' => '[]', 'created_at' => '2025-02-05 15:39:42', 'updated_at' => '2025-02-05 15:39:42'],
        ['order_id' => 92, 'product_id' => 13, 'product_name' => '2 gold creatine + shaker', 'product_image' => 'storage/products_images/0ePi65kVJ35xXEvOsgIgdslbTSz1gkDm8EB5e6Nd.jpg', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '[]', 'created_at' => '2025-02-17 20:32:34', 'updated_at' => '2025-02-17 20:32:34'],
        ['order_id' => 93, 'product_id' => 15, 'product_name' => '2 nitrotech', 'product_image' => 'storage/products_images/huQPdz6ZFFvUnPhEHIiHQXWlEU8RTaGfHZffvJAw.jpg', 'quantity' => 1, 'price' => '99.00', 'selected_options' => '{"Flavor":"Chocolate"}', 'created_at' => '2025-02-20 01:31:22', 'updated_at' => '2025-02-20 01:31:22'],
        ['order_id' => 94, 'product_id' => 13, 'product_name' => '2 gold creatine + shaker', 'product_image' => 'storage/products_images/0ePi65kVJ35xXEvOsgIgdslbTSz1gkDm8EB5e6Nd.jpg', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '[]', 'created_at' => '2025-02-21 06:23:46', 'updated_at' => '2025-02-21 06:23:46'],
        ['order_id' => 100, 'product_id' => 101, 'product_name' => 'SCITEC WATER CUT (100 CAPSULE)', 'product_image' => 'storage/products_images/Gw24Ivd0CETqPU1zcAOVKcGuLl7EegLOSlZuexdV.png', 'quantity' => 1, 'price' => '30.00', 'selected_options' => '[]', 'created_at' => '2025-03-02 11:45:04', 'updated_at' => '2025-03-02 11:45:04'],
        ['order_id' => 105, 'product_id' => 114, 'product_name' => 'INNER ARMOUR CREATINE (133 SERVINGS)', 'product_image' => 'storage/products_images/LHDO0qq5duqPBRL3J1WHyH45QkLkEPc7WvussMwT.png', 'quantity' => 1, 'price' => '20.00', 'selected_options' => '[]', 'created_at' => '2025-03-18 05:57:24', 'updated_at' => '2025-03-18 05:57:24'],
        ['order_id' => 107, 'product_id' => 114, 'product_name' => 'INNER ARMOUR CREATINE (133 SERVINGS)', 'product_image' => 'storage/products_images/LHDO0qq5duqPBRL3J1WHyH45QkLkEPc7WvussMwT.png', 'quantity' => 1, 'price' => '20.00', 'selected_options' => '[]', 'created_at' => '2025-03-25 18:16:42', 'updated_at' => '2025-03-25 18:16:42'],
        ['order_id' => 107, 'product_id' => 217, 'product_name' => 'Ashwaganda Swanson 100 capsules', 'product_image' => 'storage/products_images/dVFakDRdYGNlr25vktslNZITL6Eb5WSUOA3ZnZSO.png', 'quantity' => 1, 'price' => '20.00', 'selected_options' => '[]', 'created_at' => '2025-03-25 18:16:42', 'updated_at' => '2025-03-25 18:16:42'],
        ['order_id' => 108, 'product_id' => 30, 'product_name' => 'LEVRONE GOLD CREATINE (300g)', 'product_image' => 'storage/products_images/yoawB4WbeFFvOU6Jur3SXYM5JgbpV5pCsiDTzIPI.png', 'quantity' => 1, 'price' => '18.00', 'selected_options' => '[]', 'created_at' => '2025-03-29 20:51:57', 'updated_at' => '2025-03-29 20:51:57'],
        ['order_id' => 114, 'product_id' => 226, 'product_name' => 'V-Shape Creatine 100 Servings', 'product_image' => 'storage/products_images/wjohlZFtpAPtz32dUa6sSbF5i0hR775bou6VWDxu.png', 'quantity' => 1, 'price' => '23.00', 'selected_options' => '{"Flavor":"Blue Raspberry"}', 'created_at' => '2025-04-10 02:16:10', 'updated_at' => '2025-04-10 02:16:10'],
        ['order_id' => 115, 'product_id' => 39, 'product_name' => 'MassTech Extreme 2000 (5.4kg)', 'product_image' => 'storage/products_images/tmfDMEgrDwewHr1eq8qZqU9dxwgEzIaPC4O7BQTU.png', 'quantity' => 1, 'price' => '65.00', 'selected_options' => '{"Flavor":"Triple chocolate  brownie"}', 'created_at' => '2025-04-14 01:22:28', 'updated_at' => '2025-04-14 01:22:28'],
        ['order_id' => 119, 'product_id' => 217, 'product_name' => 'Ashwaganda Swanson 100 capsules', 'product_image' => 'storage/products_images/dVFakDRdYGNlr25vktslNZITL6Eb5WSUOA3ZnZSO.png', 'quantity' => 1, 'price' => '20.00', 'selected_options' => '[]', 'created_at' => '2025-04-19 22:44:58', 'updated_at' => '2025-04-19 22:44:58'],
        ['order_id' => 119, 'product_id' => 13, 'product_name' => '2 gold creatine + shaker', 'product_image' => 'storage/products_images/0ePi65kVJ35xXEvOsgIgdslbTSz1gkDm8EB5e6Nd.jpg', 'quantity' => 1, 'price' => '35.00', 'selected_options' => '[]', 'created_at' => '2025-04-19 22:44:58', 'updated_at' => '2025-04-19 22:44:58'],
    ];

    public function up(): void
    {
        $orderIds = collect($this->lines)->pluck('order_id')->unique();
        $emptyOrders = DB::table('orders')->whereIn('id', $orderIds)
            ->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('order_items')->whereColumn('order_items.order_id', 'orders.id'))
            ->pluck('id')->flip();
        $existingProducts = DB::table('products')->whereIn('id', collect($this->lines)->pluck('product_id'))->pluck('id')->flip();

        $rows = collect($this->lines)
            ->filter(fn ($line) => isset($emptyOrders[$line['order_id']]))
            ->map(fn ($line) => array_merge($line, [
                'product_id' => isset($existingProducts[$line['product_id']]) ? $line['product_id'] : null,
            ]))
            ->values()->all();

        if ($rows) {
            DB::table('order_items')->insert($rows);
        }
    }

    public function down(): void
    {
        // Rolling back means going back to the old code, which can't display order lines whose product
        // no longer exists. Remove exactly the lines this migration inserted.
        foreach ($this->lines as $line) {
            DB::table('order_items')
                ->where('order_id', $line['order_id'])
                ->whereNull('product_id')
                ->where('product_name', $line['product_name'])
                ->where('quantity', $line['quantity'])
                ->where('price', $line['price'])
                ->where('created_at', $line['created_at'])
                ->delete();
        }
    }
};
