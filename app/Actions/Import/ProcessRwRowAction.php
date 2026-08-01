<?php

namespace App\Actions\Import;

use App\Models\Rw;

class ProcessRwRowAction
{
    public function execute(array $row): bool
    {
        $code = ! empty($row['kode_rw']) ? trim((string) $row['kode_rw']) : (! empty($row['rw']) ? trim((string) $row['rw']) : null);

        if (! $code) {
            return false;
        }

        $name = ! empty($row['nama_rw']) ? trim((string) $row['nama_rw']) : ("RW " . $code);
        $statusStr = strtolower(trim((string) ($row['status'] ?? 'aktif')));
        $isActive = $statusStr !== 'non-aktif' && $statusStr !== '0' && $statusStr !== 'false';

        Rw::updateOrCreate(
            ['code' => $code],
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
