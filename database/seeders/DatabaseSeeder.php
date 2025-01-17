<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\GradeLevel;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategoriesTableSeeder::class,
            GradeLevelsTableSeeder::class,
            // ReviewSeeder::class,
            // CourseEnrollSeeder::class,
//            WithdrawRequestSeeder::class
        ]);
    }
}
