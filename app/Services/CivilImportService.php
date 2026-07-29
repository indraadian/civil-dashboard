<?php

namespace App\Services;

use App\Http\Requests\ImportCivilRequest;
use App\Jobs\ProcessCivilImportJob;
use App\Models\CivilImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class CivilImportService
{
    /**
     * Inisiasi proses import:
     * 1. Simpan file ke storage
     * 2. Buat record import (status: pending)
     * 3. Dispatch job ke queue
     *
     * @return CivilImport Record import yang baru dibuat
     */
    public function initiate(ImportCivilRequest $request): CivilImport
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

        Log::info('CivilImport dimulai.', [
            'import_id' => $import->id,
            'filename'  => $filename,
            'path'      => $path,
            'user_id'   => $import->created_by,
        ]);

        ProcessCivilImportJob::dispatch($import);

        return $import;
    }

    /**
     * Batalkan import yang masih pending.
     */
    public function cancel(CivilImport $import): void
    {
        if (! $import->isPending()) {
            return;
        }

        $import->update(['status' => 'cancelled']);

        Log::info('CivilImport dibatalkan.', ['import_id' => $import->id]);
    }
}
