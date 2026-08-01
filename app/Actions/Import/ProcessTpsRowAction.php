<?php

namespace App\Actions\Import;

use App\Models\Tps;

class ProcessTpsRowAction
{
    public function execute(array $row): bool
    {
        $code = ! empty($row['kode_tps']) ? trim((string) $row['kode_tps']) : (! empty($row['tps']) ? trim((string) $row['tps']) : null);
        $name = ! empty($row['nama_tps']) ? trim((string) $row['nama_tps']) : ($code ? "TPS {$code}" : null);

        if (! $code && ! $name) {
            return false;
        }

        Tps::updateOrCreate(
            ['code' => $code ?: $name],
            [
                'name' => $name ?: "TPS {$code}",
                'location' => $row['lokasi'] ?? $row['alamat'] ?? null,
                'total_voters' => isset($row['total_dpt']) ? (int) $row['total_dpt'] : (isset($row['total_pemilih']) ? (int) $row['total_pemilih'] : 0),
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
