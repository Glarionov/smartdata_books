<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 20; $i++) {
            $year = 1000 + rand(100, 800) + $i;
            DB::table('books')->insert([
                'name' => $this->generateName(),
                'publish_date' => "$year-01-01"
            ]);
        }
    }

    public function generateName() {
        $first = ['when', 'why', 'how'];
        $second = ['wife', 'husband', 'girlfriend', 'boyfriend'];
        $third = ['loves', 'likes', 'hates'];
        $fourth = ['dogs', 'cats', 'birds'];
        return $first[array_rand($first)] . ' my ' . $second[array_rand($second)] .
            ' ' . $third[array_rand($third)] . ' ' . $fourth[array_rand($fourth)];
    }
}
