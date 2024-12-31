<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CoursesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $faker = Faker::create();

        $userId = Auth::user()->id;

        // Define some sample categories and grade levels (Make sure these already exist in your database)
        $categories = DB::table('categories')->pluck('id')->toArray();
        $gradeLevels = DB::table('grade_levels')->pluck('id')->toArray();

        // Insert sample courses into the courses table
        DB::table('courses')->insert([
            [
                'user_id' => $userId,
                'category_id' => $faker->randomElement($categories),
                'grade_level_id' => $faker->randomElement($gradeLevels),
                'name' => 'Introduction to Math',
                'description' => 'A beginner level math course to understand the basics.',
                'price' => 50.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'category_id' => $faker->randomElement($categories),
                'grade_level_id' => $faker->randomElement($gradeLevels),
                'name' => 'Advanced English Literature',
                'description' => 'An advanced course in English literature, focusing on classic novels.',
                'price' => 80.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'user_id' => $userId,
                'category_id' => $faker->randomElement($categories),
                'grade_level_id' => $faker->randomElement($gradeLevels),
                'name' => 'Basic Science for Beginners',
                'description' => 'A basic science course for students interested in learning about the natural world.',
                'price' => 60.00,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
