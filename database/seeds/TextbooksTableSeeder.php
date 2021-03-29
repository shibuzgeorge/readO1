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
        $textbookspm1 = Textbook::create([
            'title' => 'Software Measurement and Estimation',
            'description' => 'Linda M. Laird M. Carol Brennan ISBN 0-471-67622-5',
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))]);

        $textbookspm2 = Textbook::create([
            'title' => 'Project Management for Information Systems (5th Edition)',
            'description' => 'James Cadle and Donald Yeates',
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))]);

        $textbookspm3 = Textbook::create([
            'title' => 'Software Measurement and Estimation 2',
            'description' => 'Software Measurement and Estimation 2',
            'file' => null
        ]);

        $textbookspm1->modules()->attach(Module::where('module_code', 'CS3360')->first());
        $textbookspm2->modules()->attach(Module::where('module_code', 'CS3360')->first());
        $textbookspm3->modules()->attach(Module::where('module_code', 'CS3360')->first());

        $textbook2 = Textbook::create([
            'title' => 'Introduction to computational intelligence',
            'description' => 'Introduction to computational intelligence',
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))]);

        $textbook3 = Textbook::create([
            'title' => 'Computational Intelligence 2',
            'description' => 'Computational Intelligence 2',
            'file' => null
        ]);

        $textbook2->modules()->attach(Module::where('module_code', 'CS3910')->first());
        $textbook3->modules()->attach(Module::where('module_code', 'CS3910')->first());


        $textbook4 = Textbook::create([
            'title' => 'Data Mining Concepts and Techniques Third Edition',
            'description' => 'Han, J., Kamber, M. and Pei, J. (2011) Data
            Mining: Concepts and Techniques, Morgan
            Kaufmann Publishers. ISBN 0123814790',
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))]);

        $textbook5 = Textbook::create([
            'title' => 'Data Mining 2',
            'description' => 'Data Mining 2',
            'file' => null
        ]);

        $textbook4->modules()->attach(Module::where('module_code', 'CS3440')->first());
        $textbook5->modules()->attach(Module::where('module_code', 'CS3440')->first());

        $textbook3 = Textbook::create([
            'title' => 'Fun AI textbook',
            'description' => 'Introduction to AI and data science',
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))]);

        $erc = ExtensiveReadingCategory::create(['name' => 'Artificial Intelligence', 'description' => 'The world of AI technology']);

        $textbook3->extensiveReadingCategories()->attach($erc);

        //Create faker textbooks
        factory(Textbook::class, 10)->create();
    }
}
