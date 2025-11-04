<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SOAModel extends Model
{
    use HasFactory;

    protected $table = 'cms_statement_of_account'; 

    protected $primaryKey = 'RecID';

    public $timestamps = false; 

    protected $fillable = [
        'SOA',
        'Remarks',
        'PassbookBal',
        'TransacBy',
        'TransacAt',
        'AccountNo',
        'CreatedBy',
        'CreatedAt',
        'Status',
        'Approved',
        'IsExcel',
        'Approver',
        'Attachment',
    ];

    protected $casts = [
        'PassbookBal' => 'float',
        'TransacAt' => 'datetime',
        'CreatedAt' => 'datetime',
        'Approved' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'CreatedBy', 'id');
    }

    public function transactBy()
    {
        return $this->belongsTo(UserModel::class, 'TransacBy', 'id');
    }

}
