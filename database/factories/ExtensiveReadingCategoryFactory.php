<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ExtensiveReadingCategory;
use Faker\Generator as Faker;

$factory->define(ExtensiveReadingCategory::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->sentence(1),
        'description' =>$faker->sentence(3),
    ];
});
