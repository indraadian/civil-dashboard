<?php

namespace App\Actions\Import;

use App\Models\QuickCount;
use App\Models\Tps;

class ProcessQuickCountRowAction
{
    public function execute(array $row): bool
    {
        $tpsCode = ! empty($row['kode_tps']) ? trim((string) $row['kode_tps']) : (! empty($row['nama_tps']) ? trim((string) $row['nama_tps']) : (! empty($row['tps']) ? trim((string) $row['tps']) : null));

        if (! $tpsCode) {
            return false;
        }

        $voteCount = isset($row['perolehan_suara']) ? (int) $row['perolehan_suara'] : (isset($row['suara_masuk']) ? (int) $row['suara_masuk'] : 0);
        $totalVoters = isset($row['total_dpt']) ? (int) $row['total_dpt'] : (isset($row['total_pemilih']) ? (int) $row['total_pemilih'] : 300);
        $notes = $row['catatan'] ?? null;

        $tps = Tps::where('code', $tpsCode)->orWhere('name', $tpsCode)->first();
        if (! $tps) {
            $tps = Tps::create([
                'code' => $tpsCode,
                'name' => "TPS {$tpsCode}",
                'total_voters' => $totalVoters,
            ]);
        }

        QuickCount::updateOrCreate(
            ['tps_id' => $tps->id],
            [
                'vote_count' => $voteCount,
                'total_voters' => $totalVoters ?: $tps->total_voters,
                'notes' => $notes,
                'created_by' => auth()->id() ?? 1,
            ]
        );

        return true;
    }

    public function executeBatch(array $rows): int
    {
        $success = 0;
        foreach ($rows as $row) {
            if ($this->execute($row)) {
                $success++;
            }
        }

        return $success;
    }
}
