<?php

namespace App\Jobs;

use App\Events\ExportCompleted;
use App\Events\ExportFailed;
use App\Models\Civil;
use App\Models\CivilExport;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Csv;

class GenerateCivilExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Jumlah percobaan ulang jika job gagal.
     */
    public int $tries = 3;

    /**
     * Timeout job dalam detik.
     */
    public int $timeout = 3600;

    /**
     * Waktu tunggu sebelum retry.
     *
     * @var array<int, int>
     */
    public array $backoff = [60, 180, 600];

    public function __construct(
        private readonly CivilExport $export,
        private readonly array $filters = [],
        private readonly string $format = 'xlsx',
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('GenerateCivilExportJob: mulai generate.', [
            'export_id' => $this->export->id,
            'filters' => $this->filters,
            'format' => $this->format,
        ]);

        try {
            $this->generateFile();
        } catch (\Throwable $e) {
            $message = $e->getMessage();
            $this->export->markAsFailed($message);
            ExportFailed::dispatch($this->export->fresh(), $message);

            Log::error('GenerateCivilExportJob: error.', [
                'export_id' => $this->export->id,
                'error' => $message,
            ]);

            throw $e;
        }
    }

    /**
     * Generate file menggunakan PhpSpreadsheet dengan streaming.
     */
    private function generateFile(): void
    {
        $totalRows = $this->buildQuery()->count();
        $this->export->markAsProcessing($totalRows);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Tulis heading row
        $sheet->fromArray($this->getHeadings(), null, 'A1');

        // Stream data dari DB menggunakan cursor (LazyCollection)
        $rowIndex = 2;
        $processedRows = 0;

        $this->buildQuery()
            ->cursor()
            ->each(function (Civil $civil) use ($sheet, &$rowIndex, &$processedRows): void {
                $sheet->fromArray($this->mapRow($civil), null, 'A' . $rowIndex);
                $rowIndex++;
                $processedRows++;

                // Update progress setiap 1000 baris
                if ($processedRows % 1000 === 0) {
                    $this->export->updateProgress($processedRows);
                }
            });

        // Simpan ke file temp
        $storedPath = $this->saveSpreadsheet($spreadsheet);
        $downloadUrl = $this->buildDownloadUrl($storedPath);

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);

        $this->export->markAsCompleted($storedPath, $downloadUrl);

        ExportCompleted::dispatch($this->export->fresh());

        Log::info('GenerateCivilExportJob: selesai.', [
            'export_id' => $this->export->id,
            'total_rows' => $processedRows,
            'path' => $storedPath,
        ]);
    }

    /**
     * Bangun query dengan filter yang diberikan.
     */
    private function buildQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Civil::query()->orderBy('updated_at', 'desc');

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['hamlet'])) {
            $query->where('hamlet', $this->filters['hamlet']);
        }

        if (!empty($this->filters['rt'])) {
            $query->where('rt', $this->filters['rt']);
        }

        if (!empty($this->filters['rw'])) {
            $query->where('rw', $this->filters['rw']);
        }

        return $query;
    }

    /**
     * Simpan spreadsheet ke storage dan kembalikan path.
     */
    private function saveSpreadsheet(Spreadsheet $spreadsheet): string
    {
        $directory = 'exports/' . now()->format('Y/m');
        $filename = $this->export->filename;
        $tempPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $filename;

        $writer = $this->format === 'csv'
            ? new Csv($spreadsheet)
            : new Xlsx($spreadsheet);

        $writer->save($tempPath);

        $storedPath = $directory . '/' . $filename;
        Storage::disk('local')->put($storedPath, file_get_contents($tempPath));

        @unlink($tempPath); // hapus temp file

        return $storedPath;
    }

    /**
     * Bangun URL download yang aman untuk file export.
     */
    private function buildDownloadUrl(string $storedPath): string
    {
        return route('civils.export.download', ['export' => $this->export->id]);
    }

    /**
     * Heading kolom untuk file export.
     *
     * @return array<int, string>
     */
    private function getHeadings(): array
    {
        return [
            'No. KK',
            'NIK',
            'Nama Lengkap',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Usia',
            'Jenis Kelamin',
            'RT',
            'RW',
            'Dusun',
            'Alamat',
            'Jenis Lokasi',
            'Status',
        ];
    }

    /**
     * Map satu model Civil ke array baris Excel.
     *
     * @return array<int, mixed>
     */
    private function mapRow(Civil $civil): array
    {
        $age = $civil->date_of_birth
            ? (int) now()->diffInYears($civil->date_of_birth)
            : '-';

        return [
            $civil->kk ? "'" . $civil->kk : '-',
            "'" . $civil->nik,
            $civil->name,
            $civil->place_of_birth ?? '-',
            $civil->date_of_birth ? Carbon::parse($civil->date_of_birth)->format('d-m-Y') : null,
            $age,
            $civil->gender ?? '-',
            "'" . $civil->rt,
            "'" . $civil->rw,
            $civil->hamlet ?? '-',
            $civil->address,
            $civil->location_type === 'village' ? 'Kampung' : ($civil->location_type === 'housing' ? 'Perumahan' : '-'),
            $civil->status ?? '-',
        ];
    }

    /**
     * Handle kegagalan setelah semua retry habis.
     */
    public function failed(\Throwable $exception): void
    {
        $message = $exception->getMessage();

        if (!$this->export->isFailed()) {
            $this->export->markAsFailed($message);
            ExportFailed::dispatch($this->export->fresh(), $message);
        }

        Log::error('GenerateCivilExportJob: job gagal setelah semua retry.', [
            'export_id' => $this->export->id,
            'error' => $message,
        ]);
    }
}
