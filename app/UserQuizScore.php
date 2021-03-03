<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserQuizScore extends Model
{
    protected $table = 'user_quiz_score';

    protected $fillable = [
         'user_id', 'quiz_id', 'attempt_number', 'result', 'score'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'result',
    ];

    /**
     * Quiz relationship
     */
    public function quiz()
    {
        return $this->belongsTo('App\Quiz', 'quiz_id');
    }

    /**
     * User relationship
     */
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id');
    }
}
