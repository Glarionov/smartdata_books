<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (['Jake', 'Jane', 'Sasha'] as $firstName) {
            foreach (['Black', 'White', 'Green'] as $lastName) {
                DB::table('authors')->insert([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'birth_date' => date("Y-m-d", mt_rand(1, time()))
                ]);
            }
        }
    }
}
