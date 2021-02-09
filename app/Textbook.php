<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Textbook extends Model
{
    protected $fillable = [
        'title', 'description', 'file',
    ];

    /**
     * Module relationship
     */
    public function modules()
    {
        return $this->belongsToMany('App\Module');
    }

    /**
     * Extensive Reading Category relationship
     */
    public function extensiveReadingCategories()
    {
        return $this->belongsToMany('App\ExtensiveReadingCategory');
    }

    /**
     * Text relationship
     */
    public function texts()
    {
        return $this->hasMany('App\Text');
    }
}
