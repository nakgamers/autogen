<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankAccount extends Model
{
    use HasFactory;

    protected $table = 'bank_accounts';
    protected $primaryKey = 'account_number';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'account_number',
        'customer_name',
        'current_noa',
        'segment',
        'rm_manager_id',
        'branch',
        'kcu_branch_name',
        'bank_name',
        'hb_period',
        
        // --- TUNGGAKAN & DENDA ---
        'principal_arrears',
        'interest_arrears',
        'penalty_fee',
        
        'billing_address',
        'billing_city',
        'fa_collateral',
        'collateral_details',
        'collateral_village',
        'collateral_district',
        'collateral_city',
        'collateral_province',
        // --------------------------------------------------------

        'notes',
    ];

    protected $casts = [
        'current_noa'           => 'integer',
        
        // --- TAMBAHAN CASTING ---
        'principal_arrears'     => 'decimal:2',
        'interest_arrears'      => 'decimal:2',
        'penalty_fee'           => 'decimal:2',
    ];

    /**
     * Mutator agar input bank_name selalu tersimpan sebagai lowercase (konvensional/syariah)
     */
    public function setBankNameAttribute($value)
    {
        $this->attributes['bank_name'] = strtolower(trim($value));
    }

    /**
     * Mutator agar input fa_collateral selalu tersimpan sebagai lowercase
     */
    public function setFaCollateralAttribute($value)
    {
        $this->attributes['fa_collateral'] = strtolower(trim($value));
    }

    /**
     * Relasi ke RM Pengelola
     */
    public function rmManager(): BelongsTo
    {
        return $this->belongsTo(RmManager::class, 'rm_manager_id');
    }
}