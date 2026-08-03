<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Candidate extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'number',
        'name',
        'photo',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'number' => 'integer',
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'photo_url',
    ];

    public function quickCountDetails(): HasMany
    {
        return $this->hasMany(QuickCountDetail::class, 'candidate_id');
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        if (str_starts_with($this->photo, 'http://') || str_starts_with($this->photo, 'https://')) {
            return $this->photo;
        }

        if (file_exists(public_path('uploads/' . $this->photo))) {
            return asset('uploads/' . $this->photo);
        }

        return Storage::disk('public')->url($this->photo);
    }
}
