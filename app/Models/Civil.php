<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Civil extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'kk',
        'nik',
        'name',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'rt',
        'rw',
        'hamlet',
        'address',
        'location_type',
        'status',
    ];

    /**
     * Scope query untuk membatasi data berdasarkan hak akses wilayah user (user_location_scopes).
     */
    public function scopeForUser($query, ?User $user)
    {
        if (!$user || $user->isAdmin()) {
            return $query;
        }

        $scopes = $user->locationScopes()->with(['rw', 'rt'])->get();

        if ($scopes->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) use ($scopes) {
            foreach ($scopes as $scope) {
                if (!$scope->rw) {
                    continue;
                }

                $rwCode = $scope->rw->code;

                if (is_null($scope->rt_id) || !$scope->rt) {
                    // Akses seluruh RT pada RW ini
                    $q->orWhere('rw', $rwCode);
                } else {
                    // Akses RT spesifik pada RW ini
                    $rtCode = $scope->rt->code;
                    $q->orWhere(function ($sub) use ($rwCode, $rtCode) {
                        $sub->where('rw', $rwCode)->where('rt', $rtCode);
                    });
                }
            }
        });
    }

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
        ];
    }
}
