<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class QuickCount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tps_id',
        'officer_name',
        'officer_phone',
        'input_at',
        'total_voters',
        'invalid_votes',
        'c1_photo',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'input_at' => 'datetime',
        'total_voters' => 'integer',
        'invalid_votes' => 'integer',
    ];

    protected $appends = [
        'c1_photo_url',
        'valid_votes',
    ];

    public function tps(): BelongsTo
    {
        return $this->belongsTo(Tps::class, 'tps_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(QuickCountDetail::class, 'quick_count_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getValidVotesAttribute(): int
    {
        return (int) $this->details->sum('vote_count');
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
    public function scopeForCurrentUser(Builder $query): Builder
    {
        $user = auth()->user();

        if (! $user) {
            return $query;
        }

        if ($user->isSuperAdmin() || $user->hasRole('Admin')) {
            return $query;
        }

        $accessibleRwIds = $user->locationScopes()
            ->pluck('rw_id')
            ->filter()
            ->unique()
            ->toArray();

        $accessibleRtIds = $user->locationScopes()
            ->pluck('rt_id')
            ->filter()
            ->unique()
            ->toArray();

        if (empty($accessibleRwIds) && empty($accessibleRtIds)) {
            return $query;
        }

        return $query->whereHas('tps', function (Builder $tpsQuery) use ($accessibleRwIds, $accessibleRtIds) {
            $tpsQuery->where(function (Builder $q) use ($accessibleRwIds, $accessibleRtIds) {
                if (! empty($accessibleRwIds)) {
                    $q->whereIn('rw_id', $accessibleRwIds);
                }
                if (! empty($accessibleRtIds)) {
                    $q->orWhereIn('rt_id', $accessibleRtIds);
                }
            });
        });
    }
}
