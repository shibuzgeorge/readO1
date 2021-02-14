<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    protected $fillable = [
        'option', 'points', 'question_id',
    ];

    protected $hidden = [
        'points',
    ];

    /**
     * Question relationship
     */
    public function question()
    {
        return $this->belongsTo('App\Question', 'question_id');
    }
}
