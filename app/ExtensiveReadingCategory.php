<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExtensiveReadingCategory extends Model
{
    protected $fillable = [
        'name', 'description',
    ];

    public function textbooks()
    {
        return $this->belongsToMany('App\Textbook');
    }
}
