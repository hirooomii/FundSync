<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ListOfBankModel extends Model
{
    use HasFactory;

    protected $table = 'cms_list_bank'; 

    protected $primaryKey = 'RecID'; 

    public $timestamps = false; 
    protected $fillable = [
        'Bank',
        'Abbreviation',
        'CreatedBy',
        'CreatedAt',
        'Status',
    ];
}
