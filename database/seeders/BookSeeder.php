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

            $bookId = $Book->id;
//            $bookId = $Book->insert([
//                'name' => $this->generateName(),
//                'publish_date' => "$year-01-01"
//            ]);

            $authorsAmount = rand(1, 3);

            for ($j = 0; $j < $authorsAmount; $j++) {
                DB::table('book_authors')->insertOrIgnore([
                    'book_id' => $bookId,
                    'author_id' => rand(1, 9)
                ]);
            }
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
