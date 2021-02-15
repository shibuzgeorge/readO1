<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Question;
use App\Quiz;
use Faker\Generator as Faker;


$factory->define(Question::class, function (Faker $faker) {
    return [
        'question' => $faker->sentence,
        'quiz_id' => factory(App\Quiz::class)->create()->id,
    ];
});
