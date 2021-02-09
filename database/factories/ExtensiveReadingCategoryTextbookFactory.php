<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\ExtensiveReadingCategoryTextbook;
use Faker\Generator as Faker;

$factory->define(ExtensiveReadingCategoryTextbook::class, function (Faker $faker) {
    return [
        'extensive_reading_category_id' => factory(\App\ExtensiveReadingCategory::class)->create()->id,
        'textbook_id' => factory(App\Textbook::class)->create()->id,
    ];
});
