<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Option;
use App\Question;
use Faker\Generator as Faker;

$factory->define(Option::class, function (Faker $faker) {
    return [
        'option' => $faker->word,
        'points' => $faker->numberBetween(0,1),
        'question_id' => factory(App\Question::class)->create()->id,
    ];
});
