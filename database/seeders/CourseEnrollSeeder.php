<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseEnroll;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Random\RandomException;

class CourseEnrollSeeder extends Seeder
{

    /**
     * @throws RandomException
     */
    public function run(): void
    {
        // Fetch 5 students and 5 courses from the database
        $students = User::where('role', 'student')->take(5)->get();
        $courses = Course::take(5)->get();

        // Ensure we don't exceed the number of available courses or students
        $count = min($students->count(), $courses->count());

        // Enroll students into courses
        foreach ($students->take($count) as $index => $student) {
            DB::table('course_enrolls')->insert([
                'course_id' => $courses[$index]->id,
                'user_id' => $student->id,
                'transaction_id' => Str::random(10),
                'amount' => rand(100, 500),
                'status' => 'completed',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

}
