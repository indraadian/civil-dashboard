<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tps extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tps';

    protected $fillable = [
        'name',
        'code',
        'location',
        'total_voters',
    ];

    public function quickCounts(): HasMany
    {
        return $this->hasMany(QuickCount::class, 'tps_id');
    }
}
