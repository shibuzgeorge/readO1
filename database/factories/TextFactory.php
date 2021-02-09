<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Text;
use App\Textbook;
use Faker\Generator as Faker;

$factory->define(Text::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(3),
        'description' => $faker->sentence(6),
        'textbook_id' => Textbook::inRandomOrder()->first()->id,
        'file' => base64_encode(file_get_contents(public_path('example.pdf')))
    ];
});
