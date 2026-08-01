<?php

namespace App\Services;

use App\Actions\Export\QuickCountExporter;
use App\Jobs\GenerateCivilExportJob;
use App\Models\CivilExport;
use Illuminate\Support\Facades\Log;

class QuickCountExportService
{
    public function initiate(int $userId, array $filters = [], string $format = 'xlsx'): CivilExport
    {
        $filename = 'quick_count_' . now()->format('Ymd_His') . '.' . $format;

        $export = CivilExport::create([
            'filename'   => $filename,
            'status'     => 'pending',
            'created_by' => $userId,
        ]);

        Log::info('QuickCountExport dimulai.', [
            'export_id' => $export->id,
            'filename'  => $filename,
            'user_id'   => $userId,
        ]);

        GenerateCivilExportJob::dispatch($export, $filters, $format, QuickCountExporter::class);

        return $export;
    }
}
