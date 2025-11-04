<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyModel extends Model
{
    protected $table = 'cms_list_company';
    protected $primaryKey = 'RecID';
    public $timestamps = false;

    protected $fillable = [
        'Company',
        'Abbreviation',
        'CreatedBy',
        'CreatedAt',
        'Status',
    ];
}
