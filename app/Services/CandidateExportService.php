<?php

namespace App\Services;

use App\Actions\Export\CandidateExporter;
use App\Jobs\GenerateCivilExportJob;
use App\Models\CivilExport;
use Illuminate\Support\Facades\Log;

class CandidateExportService
{
    public function initiate(int $userId, array $filters = [], string $format = 'xlsx'): CivilExport
    {
        $filename = 'master_candidate_' . now()->format('Ymd_His') . '.' . $format;

        $export = CivilExport::create([
            'filename'   => $filename,
            'status'     => 'pending',
            'created_by' => $userId,
        ]);

        Log::info('CandidateExport dimulai.', [
            'export_id' => $export->id,
            'filename'  => $filename,
            'user_id'   => $userId,
        ]);

        GenerateCivilExportJob::dispatch($export, $filters, $format, CandidateExporter::class);

        return $export;
    }
}
