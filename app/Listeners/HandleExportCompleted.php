<?php

namespace App\Listeners;

use App\Events\ExportCompleted;
use App\Notifications\ExportCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleExportCompleted implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ExportCompleted $event): void
    {
        $export = $event->export;

        Log::info('CivilExport selesai.', [
            'export_id'  => $export->id,
            'filename'   => $export->filename,
            'total_rows' => $export->total_rows,
            'expires_at' => $export->expires_at?->toIso8601String(),
        ]);

        $export->creator->notify(new ExportCompletedNotification($export));
    }

    /**
     * Handle a job failure.
     */
    public function failed(ExportCompleted $event, \Throwable $exception): void
    {
        Log::error('Gagal mengirim notifikasi ExportCompleted.', [
            'export_id' => $event->export->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}
