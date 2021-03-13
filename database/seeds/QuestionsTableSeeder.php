<?php

use App\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class QuestionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Question::class, 20)->create();
    }
}
