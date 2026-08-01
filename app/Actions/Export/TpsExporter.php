<?php

namespace App\Actions\Export;

use App\Models\Tps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TpsExporter implements ExporterInterface
{
    public function getHeadings(): array
    {
        return [
            'Kode TPS',
            'Nama TPS',
            'Lokasi',
            'Total DPT / Pemilih',
        ];
    }

    public function buildQuery(array $filters = []): Builder
    {
        return Tps::query()->orderBy('code', 'asc');
    }

    public function mapRow(Model $model): array
    {
        /** @var Tps $tps */
        $tps = $model;

        return [
            $tps->code,
            $tps->name,
            $tps->location ?? '-',
            $tps->total_voters ?? 0,
        ];
    }
}
