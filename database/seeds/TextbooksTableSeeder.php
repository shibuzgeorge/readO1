<?php

use App\Textbook;
use App\ModuleTextbook;
use App\Module;
use Illuminate\Database\Seeder;

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

        ModuleTextbook::create(['module_id' => Module::where('module_code', 'CS3360')->first()->id, 'textbook_id' => $textbook1->id]);

        $textbook2 = Textbook::create([
            'title' => 'Introduction to computational intelligence',
            'description' => 'Introduction to computational intelligence',
            'file' => base64_encode(file_get_contents(public_path('example.pdf')))]);

        ModuleTextbook::create(['module_id' => Module::where('module_code', 'CS3910')->first()->id, 'textbook_id' => $textbook2->id]);

        //Create faker textbooks
        factory(Textbook::class, 10)->create();
    }
}
