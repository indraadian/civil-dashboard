<?php

namespace App\Services;

use App\Actions\Export\TpsExporter;
use App\Jobs\GenerateCivilExportJob;
use App\Models\CivilExport;
use Illuminate\Support\Facades\Log;

class TpsExportService
{
    public function initiate(int $userId, array $filters = [], string $format = 'xlsx'): CivilExport
    {
        $filename = 'master_tps_' . now()->format('Ymd_His') . '.' . $format;

        $export = CivilExport::create([
            'filename'   => $filename,
            'status'     => 'pending',
            'created_by' => $userId,
        ]);

        Log::info('TpsExport dimulai.', [
            'export_id' => $export->id,
            'filename'  => $filename,
            'user_id'   => $userId,
        ]);

        GenerateCivilExportJob::dispatch($export, $filters, $format, TpsExporter::class);

        return $export;
    }
}
