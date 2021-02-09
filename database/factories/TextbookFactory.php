<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Textbook;
use Faker\Generator as Faker;

$factory->define(Textbook::class, function (Faker $faker) {
    return [
        'title' => $faker->sentence(3),
        'description' => $faker->sentence(6),
    ];
});
