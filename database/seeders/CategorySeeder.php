<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            'Shilajit',
            'Offers',
            'XPN SUPPLEMENTS',
            'Whey Protein',
            'Isolate Whey Protein',
            'Creatine',
            'Shakers',
            'Vitamins',
            'Testosterone Boosters',
            'Pre-Workout',
            'Lean Mass Builders',
            'Mass Gainers',
            'Arginine & Citrulline',
            'Fishoil',
            'Fat Burners',
            'Amino Acids',
            'Anti Aging',
            'BCAA',
            'Collagen',
            'Carbs',
            'Ashwagandha',
            'Beef Protein',
            'Melatonin',
            'ZMA',
            'Universal',
            'STRESS RELIEF',
        ];

        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
