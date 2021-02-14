<?php

use App\Quiz;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class QuizzesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Quiz::class, 10)->create();
    }
}
