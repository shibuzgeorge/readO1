<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Quiz;
use App\Text;
use Faker\Generator as Faker;

$factory->define(Quiz::class, function (Faker $faker) {
    return [
        'max_points' => $faker->numberBetween(1,10),
        'text_id' => factory(App\Text::class)->create()->id,
    ];
});
