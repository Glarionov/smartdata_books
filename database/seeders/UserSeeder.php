<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([[
            'login' => 'babab',
            'password' => Hash::make('babab'),
        ],[
            'login' => 'admin',
            'password' => Hash::make('admin'),
        ]]);

        DB::table('users_with_extra_accesses')->insert([
            'user_id' => '2',
        ]);
    }
}
