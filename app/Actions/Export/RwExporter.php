<?php

namespace App\Actions\Export;

use App\Models\Rw;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RwExporter implements ExporterInterface
{
    public function getHeadings(): array
    {
        return [
            'Kode RW',
            'Nama RW',
            'Status',
        ];
    }

    public function buildQuery(array $filters = []): Builder
    {
        return Rw::query()->orderBy('code', 'asc');
    }

    public function mapRow(Model $model): array
    {
        /** @var Rw $rw */
        $rw = $model;

        return [
            $rw->code,
            $rw->name,
            $rw->is_active ? 'Aktif' : 'Non-Aktif',
        ];
    }
}
