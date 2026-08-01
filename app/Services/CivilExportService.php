<?php

namespace App\Services;

use App\Jobs\GenerateCivilExportJob;
use App\Models\CivilExport;
use Illuminate\Support\Facades\Log;

class CivilExportService
{
    /**
     * Inisiasi proses export:
     * 1. Buat record export (status: pending)
     * 2. Dispatch job ke queue
     *
     * @return CivilExport Record export yang baru dibuat
     */
    public function initiate(
        int $userId,
        array $filters = [],
        string $format = 'xlsx',
        ?string $exporterClass = null,
        string $prefix = 'civils'
    ): CivilExport {
        $filename = $prefix . '_' . now()->format('Ymd_His') . '.' . $format;

        $export = CivilExport::create([
            'filename'   => $filename,
            'status'     => 'pending',
            'created_by' => $userId,
        ]);

        Log::info('Export dimulai.', [
            'export_id' => $export->id,
            'filename'  => $filename,
            'filters'   => $filters,
            'user_id'   => $userId,
        ]);

        GenerateCivilExportJob::dispatch($export, $filters, $format, $exporterClass);

        return $export;
    }
}
