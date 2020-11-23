<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BookAuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($bookId = 1; $bookId < 21; $bookId++) {
            $authorsAmount = rand(1, 3);

            for ($j = 0; $j < $authorsAmount; $j++) {
                DB::table('book_authors')->insertOrIgnore([
                    'book_id' => $bookId,
                    'author_id' => rand(1, 9)
                ]);
            }
        }
    }
}
