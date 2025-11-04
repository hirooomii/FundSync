<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CmsBankBalance extends Model
{
    use HasFactory;

    protected $table = 'cms_bank_balances';

    protected $fillable = [
        'account_no',
        'currency',
        'opening_balance',
        'closing_balance',
        'available_balance',
        'transaction_date',
        'SOAID',
    ];
    
    protected $casts = [
        'opening_balance' => 'float',
        'closing_balance' => 'float',
        'available_balance' => 'float',
        'transaction_date' => 'date',
    ];

     public $timestamps = false;
}
