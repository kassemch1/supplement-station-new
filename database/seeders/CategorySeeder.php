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
            'Whey Protein',
            'Whey Protein Isolate',
            'Creatine',
            'Mass Gainers',
            'Offers',
            'Amino Acids',
            'Testosterone Boosters',
            'EAA & BCAA',
            'Pre-Workout',
            'Herbs',
            'Vitamins',
            'Fat Burner',
            'Carbs',
            'Beef Protein',
            'Collagen',
            'Shakers',
            'Gym Gear',
            'Shirts',
            'Protein Bars',

           
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
