<?php

namespace App\Listeners;

use App\Events\ImportCompleted;
use App\Notifications\ImportCompletedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class HandleImportCompleted implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ImportCompleted $event): void
    {
        $import = $event->import;

        Log::info('CivilImport selesai.', [
            'import_id'      => $import->id,
            'total_rows'     => $import->total_rows,
            'processed_rows' => $import->processed_rows,
            'failed_rows'    => $import->failed_rows,
            'duration_s'     => $import->started_at?->diffInSeconds($import->finished_at),
        ]);

        $import->creator->notify(new ImportCompletedNotification($import));
    }

    /**
     * Handle a job failure.
     */
    public function failed(ImportCompleted $event, \Throwable $exception): void
    {
        Log::error('Gagal mengirim notifikasi ImportCompleted.', [
            'import_id' => $event->import->id,
            'error'     => $exception->getMessage(),
        ]);
    }
}
