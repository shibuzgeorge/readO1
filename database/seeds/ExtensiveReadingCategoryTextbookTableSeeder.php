<?php

use App\ExtensiveReadingCategoryTextbook;
use Illuminate\Database\Seeder;

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
