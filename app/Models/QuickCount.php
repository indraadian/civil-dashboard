<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class QuickCount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tps_id',
        'vote_count',
        'total_voters',
        'c1_photo',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $appends = [
        'c1_photo_url',
    ];

    public function tps(): BelongsTo
    {
        return $this->belongsTo(Tps::class, 'tps_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getC1PhotoUrlAttribute(): ?string
    {
        if (! $this->c1_photo) {
            return null;
        }

        if (str_starts_with($this->c1_photo, 'http://') || str_starts_with($this->c1_photo, 'https://')) {
            return $this->c1_photo;
        }

        return Storage::disk('public')->url($this->c1_photo);
    }

    /**
     * Scope query based on user role and permissions.
     */
    public function scopeForUser(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $query;
        }

        // Regular user access scope
        return $query;
    }
}
