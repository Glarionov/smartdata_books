<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;
use Illuminate\Support\Facades\DB;

class BookSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        for ($i = 0; $i < 20; $i++) {
            $Book = new Book();
            $year = 1000 + rand(100, 800) + $i;

            $Book->name = $this->generateName();
            $Book->publish_date = "$year-01-01"; //todo use faker

            $Book->save();
        }
    }

    public function generateName() {
        $second = ['wife', 'husband', 'girlfriend', 'boyfriend'];
        $third = ['loves', 'likes', 'hates'];
        $fourth = ['dogs', 'cats', 'birds'];
        return 'The reason why my ' . $second[array_rand($second)] .
            ' ' . $third[array_rand($third)] . ' ' . $fourth[array_rand($fourth)];
    }
}
