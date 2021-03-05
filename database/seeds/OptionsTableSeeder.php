<?php

use App\Option;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class OptionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Option::class, 50)->create();
    }
}
