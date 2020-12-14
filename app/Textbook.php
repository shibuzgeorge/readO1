<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Textbook extends Model
{
    /**
     * Module relationship
     */
    public function modules()
    {
        return $this->belongsToMany('App\Module');
    }
}
