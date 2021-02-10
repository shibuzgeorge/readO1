<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    protected $fillable = [
        'text_id',
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
}
