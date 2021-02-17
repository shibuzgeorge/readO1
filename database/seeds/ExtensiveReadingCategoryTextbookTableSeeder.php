<?php

use App\ExtensiveReadingCategoryTextbook;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ExtensiveReadingCategoryTextbookTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ExtensiveReadingCategoryTextbook::class, 5)->create();
    }
}
