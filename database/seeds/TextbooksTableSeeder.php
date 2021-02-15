<?php

use App\ExtensiveReadingCategory;
use App\ExtensiveReadingCategoryTextbook;
use App\Textbook;
use App\ModuleTextbook;
use App\Module;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TextbooksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $textbook1 = Textbook::create([
            'title' => 'Introduction to software project management',
            'description' => 'Introduction to software project management',
            'file' => base64_encode(file_get_contents(public_path('example.pdf')))]);

        $textbook1->modules()->attach(Module::where('module_code', 'CS3360')->first());

        $textbook2 = Textbook::create([
            'title' => 'Introduction to computational intelligence',
            'description' => 'Introduction to computational intelligence',
            'file' => base64_encode(file_get_contents(public_path('example.pdf')))]);

        $textbook2->modules()->attach(Module::where('module_code', 'CS3910')->first());

        $textbook3 = Textbook::create([
            'title' => 'Fun AI textbook',
            'description' => 'Introduction to AI and data science',
            'file' => base64_encode(file_get_contents(public_path('example.pdf')))]);

        $erc = ExtensiveReadingCategory::create(['name' => 'Artificial Intelligence', 'description' => 'The world of AI technology']);

        $textbook3->extensiveReadingCategories()->attach($erc);

        //Create faker textbooks
        factory(Textbook::class, 10)->create();
    }
}
