<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\YearGroup;
use Faker\Generator as Faker;

$factory->define(YearGroup::class, function (Faker $faker) {
    return [
        'name' => 'Year '.$faker->unique()->numberBetween(4)
    ];
});
