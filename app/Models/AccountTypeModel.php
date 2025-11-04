<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountTypeModel extends Model
{
    protected $table = 'cms_list_account_type';
    protected $primaryKey = 'RecID';
    public $timestamps = false;

    protected $fillable = [
        'AccountType',
        'Abbreviation',
        'CreatedBy',
        'CreatedAt',
        'Status',
    ];
}
