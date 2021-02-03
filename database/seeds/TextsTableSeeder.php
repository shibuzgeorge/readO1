<?php

use App\Text;
use Illuminate\Database\Seeder;

class TextsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        Text::create([
            'title' => 'Chapter 1',
            'description' => 'Chapter 1',
            'textbook_id' => \App\Textbook::where('title', 'Introduction to software project management')->first()->id,
            'file' => base64_encode(file_get_contents(public_path('example.pdf')))]);

        Text::create([
            'title' => 'Chapter 2',
            'description' => 'Chapter 2',
            'textbook_id' => \App\Textbook::where('title', 'Introduction to software project management')->first()->id,
            'file' => base64_encode(file_get_contents(public_path('example.pdf')))]);

        Text::create([
            'title' => 'Chapter 1',
            'description' => 'Chapter 1',
            'textbook_id' => \App\Textbook::where('title', 'Introduction to computational intelligence')->first()->id,
            'file' => base64_encode(file_get_contents(public_path('example.pdf')))]);

        Text::create([
            'title' => 'Chapter 2',
            'description' => 'Chapter 2',
            'textbook_id' => \App\Textbook::where('title', 'Introduction to computational intelligence')->first()->id,
            'file' => base64_encode(file_get_contents(public_path('example.pdf')))]);

        //Create faker texts
        factory(Text::class, 20)->create();
    }
}
