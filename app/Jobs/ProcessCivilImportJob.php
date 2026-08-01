<?php

namespace App\Jobs;

use App\Actions\Import\ProcessCivilRowAction;
use App\Events\ImportCompleted;
use App\Events\ImportFailed;
use App\Models\CivilImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class ProcessCivilImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika job gagal.
     */
    public int $tries = 3;

    /**
     * Timeout job dalam detik (1 jam — cukup untuk 100K+ rows).
     */
    public int $timeout = 3600;

    /**
     * Waktu tunggu sebelum retry (dalam detik).
     *
     * @var array<int, int>
     */
    public array $backoff = [30, 120, 300];

    public function __construct(
        private readonly CivilImport $import,
        private readonly ?string $actionClass = null,
    ) {}

    /**
     * Execute the job.
     */
    public function handle(ProcessCivilRowAction $defaultAction): void
    {
        $this->import->markAsProcessing();

        $action = $this->actionClass ? app($this->actionClass) : $defaultAction;

        Log::info('ProcessCivilImportJob: mulai proses.', [
            'import_id'   => $this->import->id,
            'path'        => $this->import->stored_path,
            'actionClass' => get_class($action),
        ]);

        try {
            $this->processFile($action);
        } catch (\Throwable $e) {
            $this->handleJobFailure($e->getMessage());
            throw $e; // biarkan Laravel menangani retry
        }
    }

    /**
     * Proses file Excel/CSV secara chunk menggunakan maatwebsite/excel.
     */
    private function processFile(mixed $action): void
    {
        $path = Storage::disk('local')->path($this->import->stored_path);

        // Ambil total baris terlebih dahulu untuk kalkulasi progress
        $totalRows = $this->countRows($path);
        $this->import->update(['total_rows' => $totalRows]);

        $processedRows = 0;
        $failedRows    = 0;
        $chunkSize     = 1000;

        // Baca file dengan LazyCollection untuk efisiensi memory
        $this->readFileAsLazyCollection($path)
            ->chunk($chunkSize)
            ->each(function (LazyCollection $chunk) use ($action, &$processedRows, &$failedRows, $totalRows): void {
                $rows = $chunk->toArray();

                // Batch upsert — satu query per chunk
                $processed = $action->executeBatch($rows);
                $failed    = count($rows) - $processed;

                $processedRows += $processed;
                $failedRows    += $failed;

                // Update progress ke DB setiap chunk
                $this->import->updateProgress($processedRows, $failedRows);

                Log::debug('ProcessCivilImportJob: chunk selesai.', [
                    'import_id'       => $this->import->id,
                    'processed_total' => $processedRows,
                    'total'           => $totalRows,
                    'progress'        => $this->import->progress,
                ]);
            });

        $this->import->markAsCompleted();

        ImportCompleted::dispatch($this->import->fresh());

        Log::info('ProcessCivilImportJob: selesai.', [
            'import_id'      => $this->import->id,
            'processed_rows' => $processedRows,
            'failed_rows'    => $failedRows,
        ]);
    }

    /**
     * Baca file Excel/CSV sebagai LazyCollection of rows (dengan heading row).
     */
    private function readFileAsLazyCollection(string $absolutePath): LazyCollection
    {
        return LazyCollection::make(function () use ($absolutePath): \Generator {
            $headings = null;

            // Gunakan PhpSpreadsheet reader langsung untuk stream tanpa load semua ke memory
            $reader = $this->createReader($absolutePath);
            $spreadsheet = $reader->load($absolutePath);
            $worksheet   = $spreadsheet->getActiveSheet();

            foreach ($worksheet->getRowIterator() as $rowIndex => $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);

                $cells = [];
                foreach ($cellIterator as $cell) {
                    $cells[] = $cell->getValue();
                }

                // Baris pertama adalah heading
                if ($rowIndex === 1) {
                    $headings = array_map(
                        fn ($h) => strtolower(str_replace(' ', '_', trim((string) $h))),
                        $cells
                    );
                    continue;
                }

                if ($headings !== null) {
                    yield array_combine($headings, $cells);
                }
            }

            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet);
        });
    }

    /**
     * Hitung jumlah baris data (tidak termasuk header).
     */
    private function countRows(string $path): int
    {
        $reader      = $this->createReader($path);
        $spreadsheet = $reader->load($path);
        $totalRows   = max(0, $spreadsheet->getActiveSheet()->getHighestRow() - 1); // minus header
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        return $totalRows;
    }

    /**
     * Buat reader PhpSpreadsheet berdasarkan ekstensi file.
     */
    private function createReader(string $path): \PhpOffice\PhpSpreadsheet\Reader\IReader
    {
        return \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
    }

    /**
     * Handle kegagalan job (dipanggil setelah semua retry habis).
     */
    public function failed(\Throwable $exception): void
    {
        $message = $exception->getMessage();

        $this->import->markAsFailed($message);

        ImportFailed::dispatch($this->import->fresh(), $message);

        Log::error('ProcessCivilImportJob: job gagal setelah semua retry.', [
            'import_id' => $this->import->id,
            'error'     => $message,
            'trace'     => $exception->getTraceAsString(),
        ]);
    }

    /**
     * Tandai import gagal (dipanggil di tengah proses sebelum throw).
     */
    private function handleJobFailure(string $message): void
    {
        if (! $this->import->isFailed()) {
            $this->import->markAsFailed($message);
        }
    }
}
