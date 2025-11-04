<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankDepositoryModel extends Model
{
    protected $table = 'cms_bank_depository';
    protected $primaryKey = 'RecID';
    protected $fillable = [
        'Company',
        'BankName',
        'AccountNo',
        'AccountName',
        'DepositType',
        'Description',
        'AccountTag',
        'InterestRate',
        'BeginningBal',
        'MaintainingBal',
        'BankBranch',
        'BankStreet',
        'BankCity',
        'BankProvince',
        'DateOpen',
        'MaturityDate',
        'DepContactNum',
        'DepContactPerson',
        'DepEmailAdd',
        'DepPosition',
        'Status',
        'CreatedBy',
        'CreatedAt'
    ];

    public $timestamps = false;
}
