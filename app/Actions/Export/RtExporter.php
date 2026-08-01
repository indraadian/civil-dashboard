<?php

namespace App\Actions\Export;

use App\Models\Rt;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RtExporter implements ExporterInterface
{
    public function getHeadings(): array
    {
        return [
            'Kode RW',
            'Kode RT',
            'Nama RT',
            'Status',
        ];
    }

    public function buildQuery(array $filters = []): Builder
    {
        return Rt::with('rw')->orderBy('code', 'asc');
    }

    public function mapRow(Model $model): array
    {
        /** @var Rt $rt */
        $rt = $model;

        return [
            $rt->rw?->code ?? '-',
            $rt->code,
            $rt->name,
            $rt->is_active ? 'Aktif' : 'Non-Aktif',
        ];
    }
}
