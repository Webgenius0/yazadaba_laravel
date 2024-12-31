<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('reviews')->insert([
            [
                'user_id' => 1,
                'course_id' => 1,
                'review' => 'This course was amazing! Highly recommend.',
                'rating' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 2,
                'course_id' => 1,
                'review' => 'Great insights and examples.',
                'rating' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => 3,
                'course_id' => 1,
                'review' => 'Good content but could be more detailed.',
                'rating' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
