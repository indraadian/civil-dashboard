<?php

namespace App\Actions\Export;

use App\Models\Candidate;
use App\Models\QuickCount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class QuickCountExporter implements ExporterInterface
{
    private array $candidates = [];

    public function __construct()
    {
        $this->candidates = Candidate::where('is_active', true)->orderBy('number')->get()->all();
    }

    public function getHeadings(): array
    {
        $headings = [
            'Kode TPS',
            'Nama TPS',
            'Nama Petugas',
            'No. HP',
            'Waktu Input',
        ];

        foreach ($this->candidates as $candidate) {
            $headings[] = 'Paslon ' . $candidate->number . ' (' . $candidate->name . ')';
        }

        return array_merge($headings, [
            'Suara Sah',
            'Suara Tidak Sah',
            'Total Pemilih',
            'Foto C1',
            'Catatan',
            'Dibuat Oleh',
        ]);
    }

    public function buildQuery(array $filters = []): Builder
    {
        $query = QuickCount::query()
            ->with(['tps', 'creator', 'details'])
            ->forCurrentUser();

        if (!empty($filters['tps_id'])) {
            $query->where('tps_id', $filters['tps_id']);
        }

        if (!empty($filters['officer_name'])) {
            $query->where('officer_name', 'like', '%' . $filters['officer_name'] . '%');
        }

        return $query->latest();
    }

    public function mapRow(Model $model): array
    {
        /** @var QuickCount $qc */
        $qc = $model;

        $row = [
            $qc->tps?->code ?? '-',
            $qc->tps?->name ?? '-',
            $qc->officer_name ?? '-',
            $qc->officer_phone ?? '-',
            $qc->input_at ? $qc->input_at->format('Y-m-d H:i:s') : '-',
        ];

        foreach ($this->candidates as $candidate) {
            $detail = $qc->details->firstWhere('candidate_id', $candidate->id);
            $row[] = $detail ? $detail->vote_count : 0;
        }

        $validVotes = (int) $qc->details->sum('vote_count');

        return array_merge($row, [
            $validVotes,
            (int) $qc->invalid_votes,
            (int) $qc->total_voters,
            $qc->c1_photo_url ?? '-',
            $qc->notes ?? '-',
            $qc->creator?->name ?? '-',
        ]);
    }
}
