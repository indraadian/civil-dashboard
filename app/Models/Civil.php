<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Civil extends Model
{
    protected $fillable = [
        'nik',
        'name',
        'hamlet',
        'location_type',
        'rt',
        'rw',
        'address',
        'date_of_birth',
        'gender',
        'status',
        'created_at',
        'updated_at'
    ];
}
