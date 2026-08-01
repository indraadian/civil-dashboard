<?php

namespace App\Services;

use App\Actions\Import\ProcessTpsRowAction;
use App\Jobs\ProcessCivilImportJob;
use App\Models\CivilImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TpsImportService
{
    public function initiate(Request $request): CivilImport
    {
        $file     = $request->file('file');
        $filename = $file->getClientOriginalName();
        $path     = $file->storeAs(
            path: 'imports/' . now()->format('Y/m'),
            name: now()->format('His_') . $filename,
            options: 'local',
        );

        $import = CivilImport::create([
            'filename'    => $filename,
            'stored_path' => $path,
            'status'      => 'pending',
            'created_by'  => $request->user()->id,
        ]);

        Log::info('TpsImport dimulai.', [
            'import_id' => $import->id,
            'filename'  => $filename,
            'path'      => $path,
            'user_id'   => $import->created_by,
        ]);

        ProcessCivilImportJob::dispatch($import, ProcessTpsRowAction::class);

        return $import;
    }
}
