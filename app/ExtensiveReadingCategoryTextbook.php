<?php

namespace App;


use Illuminate\Database\Eloquent\Relations\Pivot;

class ExtensiveReadingCategoryTextbook extends Pivot
{
    protected $fillable = [
         'extensive_reading_category_id', 'textbook_id',
    ];
}
