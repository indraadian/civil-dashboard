<?php

namespace App\Actions\Export;

use App\Models\Candidate;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CandidateExporter implements ExporterInterface
{
    public function getHeadings(): array
    {
        return [
            'Nomor Urut',
            'Nama Pasangan Calon',
            'Status Aktif',
        ];
    }

    public function buildQuery(array $filters = []): Builder
    {
        return Candidate::query()->orderBy('number', 'asc');
    }

    public function mapRow(Model $model): array
    {
        /** @var Candidate $candidate */
        $candidate = $model;

        return [
            $candidate->number,
            $candidate->name,
            $candidate->is_active ? 'Aktif' : 'Nonaktif',
        ];
    }
}
