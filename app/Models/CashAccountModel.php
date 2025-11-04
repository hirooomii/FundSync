<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAccountModel extends Model
{
    protected $table = 'bank_cash_account'; 
    protected $primaryKey = 'RecID';
    public $timestamps = false; 

    protected $fillable = [
        'Company',
        'Branch',
        'BranchID',
        'CashAccount',
        'Description',
        'IsActive',
        'AccountNo',
        'CreatedBy',
        'CreatedAt',
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'CreatedBy', 'id');
    }
}
