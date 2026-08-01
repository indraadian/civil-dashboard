<?php

namespace App\Services;

use App\Actions\Import\ProcessUserRowAction;
use App\Jobs\ProcessCivilImportJob;
use App\Models\CivilImport;
use Illuminate\Http\Request;

class UserImportService
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

        ProcessCivilImportJob::dispatch($import, ProcessUserRowAction::class);

        return $import;
    }
}
