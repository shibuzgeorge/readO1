<?php

use App\Option;
use App\Question;
use App\Quiz;
use App\Text;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class TextsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $text1 = Text::create([
            'title' => 'Chapter 1',
            'description' => 'Chapter 1',
            'textbook_id' => \App\Textbook::where('title', 'Software Project Management 2nd Edition')->first()->id,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))]);

        $quiz_object1 = Quiz::create(['text_id' => $text1->id]);
        $create_question1 = Question::create(['question' => 'What is software project management', 'quiz_id' => $quiz_object1->id]);
        $create_question2 = Question::create(['question' => 'Why is software project management important', 'quiz_id' => $quiz_object1->id]);

        Option::create(['option' => 'Collaboration among team members on an on-going basis',
            'question_id' => $create_question1->id, 'points' => 0]);
        Option::create(['option' => 'Software project management is an art and science of planning and leading software projects',
            'question_id' => $create_question1->id, 'points' => 1]);
        Option::create(['option' => 'Where software is created and tested',
            'question_id' => $create_question1->id, 'points' => 0]);
        Option::create(['option' => 'Where software is tested',
            'question_id' => $create_question1->id, 'points' => 0]);

        Option::create(['option' => 'It is needed to help create perfect software',
            'question_id' => $create_question2->id, 'points' => 0]);
        Option::create(['option' => 'It is needed to find the best problem solving solution',
            'question_id' => $create_question2->id, 'points' => 0]);
        Option::create(['option' => 'It is needed to manage team effectively',
            'question_id' => $create_question2->id, 'points' => 1]);
        Option::create(['option' => 'It is needed to boost team moral',
            'question_id' => $create_question2->id, 'points' => 0]);

        Text::create([
            'title' => 'Chapter 2',
            'description' => 'Chapter 2',
            'textbook_id' => \App\Textbook::where('title', 'Software Project Management 2nd Edition')->first()->id,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))]);

        Text::create([
            'title' => 'Chapter 1',
            'description' => 'Chapter 1',
            'textbook_id' => \App\Textbook::where('title', 'Project Management for Information Systems (5th Edition)')->first()->id,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/PMIS/chapter1.pdf')))]);

        Text::create([
            'title' => 'Chapter 2',
            'description' => 'Chapter 2',
            'textbook_id' => \App\Textbook::where('title', 'Project Management for Information Systems (5th Edition)')->first()->id,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/PMIS/chapter2.pdf')))]);

        Text::create([
            'title' => 'Chapter 1',
            'description' => 'Chapter 1',
            'textbook_id' => \App\Textbook::where('title', 'Introduction to computational intelligence')->first()->id,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))]);

        Text::create([
            'title' => 'Chapter 2',
            'description' => 'Chapter 2',
            'textbook_id' => \App\Textbook::where('title', 'Introduction to computational intelligence')->first()->id,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))]);

        Text::create([
            'title' => 'Chapter 1',
            'description' => 'Chapter 1',
            'textbook_id' => \App\Textbook::where('title', 'Data Mining Concepts and Techniques Third Edition')->first()->id,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))]);

        Text::create([
            'title' => 'Chapter 2',
            'description' => 'Chapter 2',
            'textbook_id' => \App\Textbook::where('title', 'Data Mining Concepts and Techniques Third Edition')->first()->id,
            'file' => base64_encode(file_get_contents(public_path('/readingMaterial/example.pdf')))]);

        //Create faker texts
        factory(Text::class, 20)->create();
    }
}
