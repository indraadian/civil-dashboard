<?php

namespace App\Actions\Import;

use App\Models\Rt;
use App\Models\Rw;

class ProcessRtRowAction
{
    public function execute(array $row): bool
    {
        $rwCode = ! empty($row['kode_rw']) ? trim((string) $row['kode_rw']) : (! empty($row['rw']) ? trim((string) $row['rw']) : null);
        $rtCode = ! empty($row['kode_rt']) ? trim((string) $row['kode_rt']) : (! empty($row['rt']) ? trim((string) $row['rt']) : null);

        if (! $rwCode || ! $rtCode) {
            return false;
        }

        $rw = Rw::firstOrCreate(
            ['code' => $rwCode],
            ['name' => "RW {$rwCode}", 'is_active' => true]
        );

        $name = ! empty($row['nama_rt']) ? trim((string) $row['nama_rt']) : ("RT " . $rtCode);
        $statusStr = strtolower(trim((string) ($row['status'] ?? 'aktif')));
        $isActive = $statusStr !== 'non-aktif' && $statusStr !== '0' && $statusStr !== 'false';

        Rt::updateOrCreate(
            ['rw_id' => $rw->id, 'code' => $rtCode],
            [
                'name' => $name,
                'is_active' => $isActive,
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
