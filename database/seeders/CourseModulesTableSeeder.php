<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CourseModulesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        // Fetch existing course IDs
        $courseIds = DB::table('courses')->pluck('id');

        // Insert sample modules for each course
        foreach ($courseIds as $courseId) {
            DB::table('course_modules')->insert([
                [
                    'course_id' => $courseId,
                    'title' => 'Module 1: Introduction to Course',
                    'video_url' => 'https://www.youtube.com/watch?v=' . $faker->lexify('??????????'),  // Generate fake YouTube video URL
                    'document_url' => $faker->url,  // Fake document URL
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'course_id' => $courseId,
                    'title' => 'Module 2: Advanced Topics in ' . $faker->word,
                    'video_url' => 'https://www.youtube.com/watch?v=' . $faker->lexify('??????????'),  // Generate fake YouTube video URL
                    'document_url' => $faker->url,  // Fake document URL
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'course_id' => $courseId,
                    'title' => 'Module 3: Conclusion and Review',
                    'video_url' => 'https://www.youtube.com/watch?v=' . $faker->lexify('??????????'),  // Generate fake YouTube video URL
                    'document_url' => $faker->url,  // Fake document URL
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }
    }
}
