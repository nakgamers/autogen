<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RmManager extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'phone_number'];

    public function bankAccounts(): HasMany
    {
        return $this->hasMany(BankAccount::class, 'rm_manager_id');
    }
}