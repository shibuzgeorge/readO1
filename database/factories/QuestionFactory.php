<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Question;
use App\Quiz;
use Faker\Generator as Faker;


$factory->define(Question::class, function (Faker $faker) {
    return [
        'question' => $faker->sentence,
        'max_points' => $faker->numberBetween(1,10),
        'quiz_id' => Quiz::where('id','!=', 1)->inRandomOrder()->first()->id,
    ];
});
