<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Quiz;
use App\Text;
use Faker\Generator as Faker;

$factory->define(Quiz::class, function (Faker $faker) {
    return [
        'text_id' => Text::inRandomOrder()->first()->id,
    ];
});
