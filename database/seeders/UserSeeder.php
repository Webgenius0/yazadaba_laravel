<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create an Admin User
        User::create([
            'name' => 'Admin User',
            'fname' => 'Admin',
            'lname' => 'User',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('12345678'),
            'is_otp_verified' => false,
            'role' => 'admin',
            'gender' => 'male',
            'bio' => 'Administrator of the system',
            'phone' => '1234567890',
            'avatar' => null,
            'email_verified_at' => Carbon::now(),
            'dob' => '25/02/2024',
        ]);

        // Create a Teacher User
        User::create([
            'name' => 'Teacher1 User',
            'fname' => 'Teacher1',
            'lname' => 'User',
            'email' => 'teacher1@gmail.com',
            'password' => Hash::make('12345678'),
            'is_otp_verified' => false,
            'role' => 'teacher',
            'gender' => 'female',
            'bio' => 'A passionate teacher',
            'phone' => '0987654321',
            'avatar' => null,
            'email_verified_at' => Carbon::now(),
            'dob' => '25/02/2024',

        ]); // Create a Teacher User
        User::create([
            'name' => 'Teacher User',
            'fname' => 'Teacher',
            'lname' => 'User',
            'email' => 'teacher2@gmail.com',
            'password' => Hash::make('12345678'),
            'is_otp_verified' => false,
            'role' => 'teacher',
            'gender' => 'female',
            'bio' => 'A passionate teacher',
            'phone' => '0987654321',
            'avatar' => null,
            'email_verified_at' => Carbon::now(),
            'dob' => '25/02/2024',

        ]); // Create a Teacher User
        User::create([
            'name' => 'Teacher User',
            'fname' => 'Teacher',
            'lname' => 'User',
            'email' => 'teacher3@gmail.com',
            'password' => Hash::make('12345678'),
            'is_otp_verified' => false,
            'role' => 'teacher',
            'gender' => 'female',
            'bio' => 'A passionate teacher',
            'phone' => '0987654321',
            'avatar' => null,
            'email_verified_at' => Carbon::now(),
            'dob' => '25/02/2024',

        ]); // Create a Teacher User
        User::create([
            'name' => 'Teacher User',
            'fname' => 'Teacher',
            'lname' => 'User',
            'email' => 'teacher4@gmail.com',
            'password' => Hash::make('12345678'),
            'is_otp_verified' => false,
            'role' => 'teacher',
            'gender' => 'female',
            'bio' => 'A passionate teacher',
            'phone' => '0987654321',
            'avatar' => null,
            'email_verified_at' => Carbon::now(),
            'dob' => '25/02/2024',

        ]); // Create a Teacher User
        User::create([
            'name' => 'Teacher User',
            'fname' => 'Teacher',
            'lname' => 'User',
            'email' => 'teacher5@gmail.com',
            'password' => Hash::make('12345678'),
            'is_otp_verified' => false,
            'role' => 'teacher',
            'gender' => 'female',
            'bio' => 'A passionate teacher',
            'phone' => '0987654321',
            'avatar' => null,
            'email_verified_at' => Carbon::now(),
            'dob' => '25/02/2024',

        ]);

        // Create a Student User
        User::create([
            'name' => 'Student User',
            'fname' => 'Student',
            'lname' => 'User',
            'email' => 'student1@gmail.com',
            'password' => Hash::make('12345678'),
            'is_otp_verified' => false,
            'role' => 'student',
            'gender' => 'male',
            'bio' => 'A dedicated student',
            'phone' => '1122334455',
            'avatar' => null,
            'email_verified_at' => Carbon::now(),
            'dob' => '25/02/2024',

        ]); User::create([
            'name' => 'Student User',
            'fname' => 'Student',
            'lname' => 'User',
            'email' => 'student2@gmail.com',
            'password' => Hash::make('12345678'),
            'is_otp_verified' => false,
            'role' => 'student',
            'gender' => 'male',
            'bio' => 'A dedicated student',
            'phone' => '1122334455',
            'avatar' => null,
            'email_verified_at' => Carbon::now(),
            'dob' => '25/02/2024',

        ]); User::create([
            'name' => 'Student User',
            'fname' => 'Student',
            'lname' => 'User',
            'email' => 'student3@gmail.com',
            'password' => Hash::make('12345678'),
            'is_otp_verified' => false,
            'role' => 'student',
            'gender' => 'male',
            'bio' => 'A dedicated student',
            'phone' => '1122334455',
            'avatar' => null,
            'email_verified_at' => Carbon::now(),
            'dob' => '25/02/2024',

        ]); User::create([
            'name' => 'Student User',
            'fname' => 'Student',
            'lname' => 'User',
            'email' => 'student4@gmail.com',
            'password' => Hash::make('12345678'),
            'is_otp_verified' => false,
            'role' => 'student',
            'gender' => 'male',
            'bio' => 'A dedicated student',
            'phone' => '1122334455',
            'avatar' => null,
            'email_verified_at' => Carbon::now(),
            'dob' => '25/02/2024',

        ]); User::create([
            'name' => 'Student User',
            'fname' => 'Student',
            'lname' => 'User',
            'email' => 'student5@gmail.com',
            'password' => Hash::make('12345678'),
            'is_otp_verified' => false,
            'role' => 'student',
            'gender' => 'male',
            'bio' => 'A dedicated student',
            'phone' => '1122334455',
            'avatar' => null,
            'email_verified_at' => Carbon::now(),
            'dob' => '25/02/2024',

        ]);
    }
}
