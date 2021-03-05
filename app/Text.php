<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
    protected $fillable = [
        'title', 'description', 'textbook_id', 'file',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'file',
    ];

    /**
     * Textbook relationship
     */
    public function textbook()
    {
        return $this->belongsTo('App\Textbook', 'textbook_id');
    }

    /**
     * Quizzes relationship
     */
    public function quizzes()
    {
        return $this->hasMany('App\Quiz');
    }
}
