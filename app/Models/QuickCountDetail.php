<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickCountDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'quick_count_id',
        'candidate_id',
        'vote_count',
    ];

    protected $casts = [
        'vote_count' => 'integer',
    ];

    public function quickCount(): BelongsTo
    {
        return $this->belongsTo(QuickCount::class, 'quick_count_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}
