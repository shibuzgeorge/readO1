<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;

$factory->define(App\ModuleTextbook::class, function (Faker $faker) {
    return [
        'module_id' => factory(App\Module::class)->create()->id,
        'textbook_id' => factory(App\Textbook::class)->create()->id,
    ];
});
