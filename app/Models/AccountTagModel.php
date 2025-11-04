<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountTagModel extends Model
{
    protected $table = 'cms_list_account_tag';
    protected $primaryKey = 'RecID';
    public $timestamps = false;

    protected $fillable = [
        'AccountTag',
        'Abbreviation',
        'CreatedBy',
        'CreatedAt',
        'Status',
    ];
    
    public function scopeActive($query)
    {
        return $query->where('Status', 1);
    }
}
