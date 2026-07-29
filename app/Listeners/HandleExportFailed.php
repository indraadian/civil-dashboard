<?php

namespace App\Listeners;

use App\Events\ExportFailed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleExportFailed implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ExportFailed $event): void
    {
        $export = $event->export;

        Log::error('CivilExport gagal.', [
            'export_id' => $export->id,
            'filename'  => $export->filename,
            'reason'    => $event->reason,
            'user_id'   => $export->created_by,
        ]);
    }

    /**
     * Handle a job failure.
     */
    public function failed(ExportFailed $event, \Throwable $exception): void
    {
        Log::critical('Gagal menangani ExportFailed event.', [
            'export_id' => $event->export->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}
