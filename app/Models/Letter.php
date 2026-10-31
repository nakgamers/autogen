<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
//use Illuminate\Database\Eloquent\Factories\HasFactory;

class Letter extends Model
{
    protected $fillable = [
        'letter_number', 
        'letter_type_id', 
        'user_id', 
        'recipient', 
        'subject', 
        'letter_date', 
        'content_data'
    ];

    protected $casts = [
        'content_data' => 'array', // Konversi JSON otomatis ke Array
        'letter_date' => 'date',
    ];

    public function letterType()
    {
        return $this->belongsTo(LetterType::class);
    }

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

