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
        // Define categories with specific names and a static image path
        $categories = [
            ['name' => 'Math', 'icon' => 'backend/images/category.png', 'status' => 'active'],
            ['name' => 'English', 'icon' => 'backend/images/category.png', 'status' => 'active'],
            ['name' => 'Science', 'icon' => 'backend/images/category.png', 'status' => 'active'],
            ['name' => 'History', 'icon' => 'backend/images/category.png', 'status' => 'active'],
            ['name' => 'Geography', 'icon' => 'backend/images/category.png', 'status' => 'active'],
            ['name' => 'Literature', 'icon' => 'backend/images/category.png', 'status' => 'active'],
            ['name' => 'Art', 'icon' => 'backend/images/category.png', 'status' => 'active'],
            ['name' => 'Music', 'icon' => 'backend/images/category.png', 'status' => 'active'],
            ['name' => 'Computer Science', 'icon' => 'backend/images/category.png', 'status' => 'active'],
            ['name' => 'Physical Education', 'icon' => 'backend/images/category.png', 'status' => 'active'],
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
