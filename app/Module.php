<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name', 'module_code', 'module_year',
    ];

    public function users(){
        return $this->belongsToMany('App\User');
    }
}
