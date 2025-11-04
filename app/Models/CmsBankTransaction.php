<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserModel;

class CmsBankTransaction extends Model
{
    use HasFactory;

    protected $table = 'cms_bank_transactions';

    protected $fillable = [
        'account_no',
        'bank_code',
        'transaction_date',
        'amount',
        'debit_or_credit',
        'transaction_type',
        'description',
        'reference',
        'additional_info',
        'company',
        'docref',
        'bindAt',
        'bindBy',
        'runningbal',
        'reconciledAmt',
        'remainingAmt',
        'comment',
        'decimal',
        'note',
        'SOAID',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'bindAt' => 'datetime',
        'amount' => 'float',
        'runningbal' => 'float',
        'reconciledAmt' => 'float',
        'remainingAmt' => 'float',
        'decimal' => 'float',
    ];

     public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'bindBy', 'id');
    }

}
