<?php

namespace Database\Seeders;

use App\Models\Book;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
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

    public function generateName() {
        $second = ['wife', 'husband', 'girlfriend', 'boyfriend'];
        $third = ['loves', 'likes', 'hates'];
        $fourth = ['dogs', 'cats', 'birds'];
        return 'The reason why my ' . $second[array_rand($second)] .
            ' ' . $third[array_rand($third)] . ' ' . $fourth[array_rand($fourth)];
    }


}
