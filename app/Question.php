<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = [
        'question', 'quiz_id', 'max_points'
    ];

    /**
     * Question relationship
     */
    public function quiz()
    {
        return $this->belongsTo('App\Quiz', 'quiz_id');
    }

    /**
     * Options relationship
     */
    public function options()
    {
        return $this->hasMany('App\Option');
    }
}
