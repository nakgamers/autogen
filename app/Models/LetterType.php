<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LetterType extends Model
{
    protected $fillable = ['code', 'name', 'format_number'];

    public function templates()
    {
        return $this->hasMany(LetterTemplate::class);
    }

    public function letters()
    {
        return $this->hasMany(Letter::class);
    }
}