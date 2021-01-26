<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'name', 'module_code', 'year_group_id'
    ];

    public function users(){
        return $this->belongsToMany('App\User');
    }

    public function textbooks(){
        return $this->belongsToMany('App\Textbook');
    }

    public function yearGroup(){
        return $this->belongsTo('App\YearGroup', 'year_group_id');
    }
}
