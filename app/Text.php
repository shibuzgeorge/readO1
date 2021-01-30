<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Text extends Model
{
    protected $fillable = [
        'title', 'description', 'textbook_id', 'file',
    ];

    /**
     * Textbook relationship
     */
    public function textbook()
    {
        return $this->belongsTo('App\Textbook', 'textbook_id');
    }
}
