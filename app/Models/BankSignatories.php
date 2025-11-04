<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\UserPositionModel;
use App\Models\UserModel;

class BankSignatories extends Model
{
    use HasFactory;

    protected $table = 'cms_bank_signatories';
    protected $primaryKey = 'RecID';
    public $timestamps = false; 
    
    protected $fillable = [
        'EmployeeID',
        'Name',
        'Position',
        'AccountNo',
        'CreatedBy',
        'CreatedAt',
        'Status',
    ];

    public function position()
    {
        return $this->belongsTo(UserPositionModel::class, 'Position', 'POSITIONCODE');
    }

    public function user()
    {
        return $this->belongsTo(UserModel::class, 'CreatedBy', 'id');
    }
}
