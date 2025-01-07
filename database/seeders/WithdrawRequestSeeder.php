<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WithdrawRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('withdraw_requests')->insert([
            [
                'user_id' => 2,
                'request_amount' => 100.50,
                'bank_info' => 'Account Information',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => 3,
                'request_amount' => 200.75,
                'bank_info' => 'Account Information',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ], [
                'user_id' => 4,
                'request_amount' => 200.75,
                'bank_info' => 'Account Information',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ], [
                'user_id' => 5,
                'request_amount' => 200.75,
                'bank_info' => 'Account Information',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ], [
                'user_id' => 6,
                'request_amount' => 200.75,
                'bank_info' => 'Account Information',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
