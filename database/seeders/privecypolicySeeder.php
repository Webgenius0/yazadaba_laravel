<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class privecypolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('privacy_policies')->insert([
            [
                'privacy_policy' => 'This course was amazing! Highly recommend.',
                'policy' => 'This course was amazing! Highly recommend.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
