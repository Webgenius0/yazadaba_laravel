<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class GradeLevelsTableSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Define grade levels
        $gradeLevels = ['Class 1', 'Class 2', 'Class 3', 'High School', 'Undergraduate', 'Postgraduate'];

        // Insert grade levels
        foreach ($gradeLevels as $level) {
            DB::table('grade_levels')->insert([
                'name' => $level,
                'status' => $faker->randomElement(['active', 'inactive']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
