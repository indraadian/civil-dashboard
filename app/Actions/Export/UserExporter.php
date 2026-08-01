<?php

namespace App\Actions\Export;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserExporter implements ExporterInterface
{
    public function getHeadings(): array
    {
        return ['Nama', 'Email', 'Role', 'Tanggal Terdaftar'];
    }

    public function buildQuery(array $filters = []): Builder
    {
        return User::query()->where('role', '!=', 'super_admin');
    }

    public function mapRow(Model $model): array
    {
        /** @var User $model */
        return [
            $model->name,
            $model->email,
            strtoupper($model->role),
            $model->created_at?->format('d M Y H:i'),
        ];
    }
}
