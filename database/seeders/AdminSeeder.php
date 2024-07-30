<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hashedPassword = Hash::make('amine2024$$$');
        DB::table('users')->insert([
            'name'=>'admin',
            'email'=>'admin@supplement-station.com',
            'password'=>$hashedPassword
        ]);
    }
}
