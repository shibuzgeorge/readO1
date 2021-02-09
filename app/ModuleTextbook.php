<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ModuleTextbook extends Pivot
{
    protected $fillable = [
        'module_id', 'textbook_id',
    ];
}
