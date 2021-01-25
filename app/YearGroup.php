<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class YearGroup extends Model
{
    protected $fillable = [
        'name'
    ];

    public function module(){
        return $this->hasMany('App\Module');
    }
}
