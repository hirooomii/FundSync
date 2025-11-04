<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserPositionModel extends Model
{
    use HasFactory;

    protected $table = 'tbl_position';
    protected $primaryKey = 'RecID';
    public $timestamps = true; 

    protected $fillable = [
        'RecID',
        'POSITIONCODE',
        'POSITIONDESC',
    ];
}
