<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterTemplate extends Model
{
    use HasFactory;

    /**
     * Kolom yang dapat diisi secara massal (Mass Assignment).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'letter_type_id',
        'title',
        'body_template',
    ];

    /**
     * Relasi Inverse Many-to-One: Template ini dimiliki oleh satu Jenis Surat (LetterType).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function letterType()
    {
        return $this->belongsTo(LetterType::class);
    }
}