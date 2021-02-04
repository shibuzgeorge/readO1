<?php

use App\ExtensiveReadingCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ExtensiveReadingCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(ExtensiveReadingCategory::class, 5)->create();
        ExtensiveReadingCategory::create(['name' => 'Other', 'description' => 'This is where all other textbooks go']);
    }
}
