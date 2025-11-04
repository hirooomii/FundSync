<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    use HasFactory;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = true; // enables created_at & updated_at

    protected $fillable = [
        'id',
        'name',
        'email',
        'created_at',
        'updated_at',
        'position',
    ];
}
