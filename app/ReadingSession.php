<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ReadingSession extends Model
{
    protected $table = 'reading_sessions';

    protected $fillable = [
        'text_id', 'user_id', 'time_taken', 'attempt_number'
    ];

    /**
     * Text relationship
     */
    public function text()
    {
        return $this->belongsTo('App\Text', 'text_id');
    }

}
