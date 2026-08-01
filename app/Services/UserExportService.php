<?php

namespace App\Services;

use App\Actions\Export\UserExporter;
use App\Jobs\GenerateCivilExportJob;
use App\Models\CivilExport;

class UserExportService
{
    public function initiate(int $userId, array $filters = [], string $format = 'xlsx'): CivilExport
    {
        $filename = 'users_' . now()->format('Ymd_His') . '.' . $format;

        $export = CivilExport::create([
            'filename'   => $filename,
            'status'     => 'pending',
            'created_by' => $userId,
        ]);

        GenerateCivilExportJob::dispatch($export, $filters, $format, UserExporter::class);

        return $export;
    }
}
