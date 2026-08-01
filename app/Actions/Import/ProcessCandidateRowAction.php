<?php

namespace App\Actions\Import;

use App\Models\Candidate;

class ProcessCandidateRowAction
{
    public function execute(array $row): bool
    {
        $number = isset($row['nomor_urut']) ? (int) $row['nomor_urut'] : (isset($row['no_urut']) ? (int) $row['no_urut'] : (isset($row['number']) ? (int) $row['number'] : null));
        $name = ! empty($row['nama_pasangan_calon']) ? trim((string) $row['nama_pasangan_calon']) : (! empty($row['nama_calon']) ? trim((string) $row['nama_calon']) : (! empty($row['name']) ? trim((string) $row['name']) : null));

        if (! $number || ! $name) {
            return false;
        }

        $rawStatus = isset($row['status_aktif']) ? strtolower((string) $row['status_aktif']) : (isset($row['status']) ? strtolower((string) $row['status']) : '1');
        $isActive = in_array($rawStatus, ['1', 'true', 'aktif', 'active', 'ya', 'yes']);

        Candidate::updateOrCreate(
            ['number' => $number],
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
