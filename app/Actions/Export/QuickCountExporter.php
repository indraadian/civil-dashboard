<?php

namespace App\Actions\Export;

use App\Models\QuickCount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QuickCountExporter implements ExporterInterface
{
    public function getHeadings(): array
    {
        return [
            'Kode TPS',
            'Nama TPS',
            'Perolehan Suara',
            'Total DPT',
            'Persentase Suara (%)',
            'Catatan',
            'Petugas Input',
        ];
    }

    public function buildQuery(array $filters = []): Builder
    {
        return QuickCount::with(['tps', 'creator'])->latest();
    }

    public function mapRow(Model $model): array
    {
        /** @var QuickCount $qc */
        $qc = $model;

        $votes = $qc->vote_count ?? 0;
        $total = $qc->total_voters ?? 0;
        $percent = $total > 0 ? round(($votes / $total) * 100, 1) : 0;

        return [
            $qc->tps?->code ?? '-',
            $qc->tps?->name ?? '-',
            $votes,
            $total,
            $percent . '%',
            $qc->notes ?? '-',
            $qc->creator?->name ?? '-',
        ];
    }
}
