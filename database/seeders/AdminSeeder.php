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

        DB::table('faqs')->insert([
           [
               'question'=>'What types of supplements do you offer?',
               'answer'=>'We offer a wide range of supplements including protein powders, amino acids, creatine, pre-workout and post-workout formulas, energy boosters, and vitamins. Our products cater to various fitness goals, from muscle building to enhanced recovery and overall wellness.'
           ] ,
            [
                'question'=>'How do I choose the right supplement for my goals?',
                'answer'=>'To choose the right supplement, start by identifying your fitness goals (e.g., muscle gain, improved endurance, or faster recovery). You can consult our product descriptions and ingredient lists to find supplements tailored to your needs. If you need personalized advice, our customer service team is available to help guide you.'
            ] ,
            [
                'question'=>'Are your supplements safe to use?',
                'answer'=>'Yes, all of our supplements are made with high-quality ingredients and adhere to stringent safety standards. We work with reputable manufacturers and ensure that our products are tested for purity and efficacy. However, if you have any specific health concerns or conditions, please consult with a healthcare professional before starting any new supplement regimen.'
            ] ,

            [
                'question'=>'Are your supplements registered with the Ministry of Health in Lebanon?',
                'answer'=>'Yes, all of our supplements are registered with the Ministry of Health in Lebanon. We ensure that our products meet the regulatory standards and guidelines set by the Ministry to guarantee their safety and efficacy. If you have any specific concerns or need further information about our product registrations, please feel free to contact us.'
            ] ,
            [
                'question'=>'Can I use multiple supplements at the same time?',
                'answer'=>'Combining supplements can be effective if done correctly. For example, you might use a protein powder for muscle recovery and a pre-workout supplement for energy. It’s important to follow recommended dosages and avoid excessive use. For personalized recommendations, you can consult with a nutritionist or contact our customer service team.'
            ],
            [
                'question'=>'How does the order process work?',
                'answer'=>'After placing your order, you will receive an email with a receipt confirming your purchase. Our team will then contact you to confirm the details of your order. Once everything is confirmed, your order will be processed and delivered to you within 2 days. If you have any questions or need assistance during the process, feel free to reach out to our customer service team.'
            ]

        ]);

        DB::table('planes')->insert([
           [
               'type'=>'Basic Wellness Plan',
               'price'=>39.99,
               'point1'=>'1 Bottle of Daily Multivitamins (30 servings)',
               'point2'=>'1 Bottle of Omega-3 Fish Oil (60 softgels)',
               'point3'=>'1 Bag of Whey Protein (1 lb)',
               'point4'=>'1 Online Training Session (30 minutes)',
               'point5'=>'Email support for general inquiries'
           ],
            [
                'type'=>'Fitness Booster Plan',
                'price'=>69.99,
                'point1'=>'1 Bottle of Pre-Workout Supplement (30 servings)',
                'point2'=>'1 Bottle of Post-Workout Recovery Formula (30 servings)',
                'point3'=>'1 Bag of BCAA Powder (1 lb)',
                'point4'=>'1 Bottle of Creatine Monohydrate (60 servings)',
                'point5'=>'2 Online Training Sessions (45 minutes each)'
            ],
            [
                'type'=>'Performance Elite Plan',
                'price'=>129.99,
                'point1'=>'1 Bottle of Pre-Workout Supplement (30 servings)',
                'point2'=>'1 Bottle of Post-Workout Recovery Formula (30 servings)',
                'point3'=>'1 Bag of Whey Protein (2 lbs)',
                'point4'=>'1 Bottle of BCAA Powder (1 lb)',
                'point5'=>'4 Online Training Sessions (1 hour each)'
            ],
        ]);
    }
}
