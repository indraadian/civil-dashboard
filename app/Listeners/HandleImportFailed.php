<?php

namespace App\Listeners;

use App\Events\ImportFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleImportFailed implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ImportFailed $event): void
    {
        $import = $event->import;

        Log::error('CivilImport gagal.', [
            'import_id' => $import->id,
            'filename'  => $import->filename,
            'reason'    => $event->reason,
            'user_id'   => $import->created_by,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(ImportFailed $event, \Throwable $exception): void
    {
        Log::critical('Gagal menangani ImportFailed event.', [
            'import_id' => $event->import->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}
