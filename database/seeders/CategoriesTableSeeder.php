<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        // Instantiate Faker
        $faker = Faker::create();

        // Define categories with specific names and images
        $categories = [
            ['name' => 'Math', 'icon' => $faker->imageUrl(100, 100, 'abstract', true, 'Math'), 'status' => 'active'],
            ['name' => 'English', 'icon' => $faker->imageUrl(100, 100, 'people', true, 'English'), 'status' => 'active'],
            ['name' => 'Science', 'icon' => $faker->imageUrl(100, 100, 'nature', true, 'Science'), 'status' => 'active'],
            ['name' => 'History', 'icon' => $faker->imageUrl(100, 100, 'city', true, 'History'), 'status' => 'active'],
            ['name' => 'Geography', 'icon' => $faker->imageUrl(100, 100, 'sports', true, 'Geography'), 'status' => 'active'],
            ['name' => 'Literature', 'icon' => $faker->imageUrl(100, 100, 'business', true, 'Literature'), 'status' => 'active'],
            ['name' => 'Art', 'icon' => $faker->imageUrl(100, 100, 'abstract', true, 'Art'), 'status' => 'active'],
            ['name' => 'Music', 'icon' => $faker->imageUrl(100, 100, 'nightlife', true, 'Music'), 'status' => 'active'],
            ['name' => 'Computer Science', 'icon' => $faker->imageUrl(100, 100, 'technics', true, 'Computer Science'), 'status' => 'active'],
            ['name' => 'Physical Education', 'icon' => $faker->imageUrl(100, 100, 'sports', true, 'Physical Education'), 'status' => 'active'],
        ];

        // Insert predefined categories into the database
        foreach ($categories as $category) {
            DB::table('categories')->insert([
                'name' => $category['name'],
                'icon' => $category['icon'],
                'status' => $category['status'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
