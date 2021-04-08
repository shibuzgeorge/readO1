<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'text_id', 'max_points'
    ];

    /**
     * Text relationship
     */
    public function text()
    {
        return $this->belongsTo('App\Text', 'text_id');
    }

    /**
     * Questions relationship
     */
    public function questions()
    {
        return $this->hasMany('App\Question');
    }

    /**
     * Options relationship
     */
    public function options()
    {
        return $this->hasManyThrough('App\Option','App\Question', 'quiz_id', 'question_id', 'id', 'id');
    }
}
