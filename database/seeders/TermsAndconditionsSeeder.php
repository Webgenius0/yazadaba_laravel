<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TermsAndconditionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('termsand_conditions')->insert([
            [
                'terms' => 'This course was amazing! Highly recommend.',
                'conditions' => 'This course was amazing! Highly recommend',
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
