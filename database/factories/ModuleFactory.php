<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Module;
use Faker\Generator as Faker;

$factory->define(Module::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->sentence(2),
        'module_code' => $faker->unique()->regexify('[A-Z]{2}').$faker->randomNumber(4),
        'year_group_id' => factory(App\YearGroup::class)->create()->id
    ];
});
