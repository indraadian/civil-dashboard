<?php

namespace App\Actions\Import;

use App\Models\Civil;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Single-responsibility action: memproses satu baris data dari file import
 * dan melakukan upsert ke tabel civils.
 */
class ProcessCivilRowAction
{
    /**
     * Proses dan simpan satu baris data warga.
     *
     * @param  array<string, mixed>  $row
     * @return bool True jika berhasil, false jika baris dilewati (misal NIK kosong).
     */
    public function execute(array $row): bool
    {
        if (empty($row['nik'])) {
            return false;
        }

        $data = $this->transformRow($row);

        Civil::upsert([$data], uniqueBy: ['nik'], update: array_keys($data));

        return true;
    }

    /**
     * Proses banyak baris sekaligus dalam satu batch upsert (lebih efisien).
     *
     * @param  array<int, array<string, mixed>>  $rows
     * @return int Jumlah baris yang berhasil diproses
     */
    public function executeBatch(array $rows): int
    {
        $transformed = [];

        foreach ($rows as $row) {
            if (empty($row['nik'])) {
                continue;
            }
            $transformed[] = $this->transformRow($row);
        }

        if (empty($transformed)) {
            return 0;
        }

        Civil::upsert($transformed, uniqueBy: ['nik'], update: array_keys($transformed[0]));

        return count($transformed);
    }

    /**
     * Transformasi raw row dari Excel ke format yang siap disimpan ke DB.
     *
     * @param  array<string, mixed>  $row
     * @return array<string, mixed>
     */
    private function transformRow(array $row): array
    {
        return [
            'nik' => $this->cleanNumeric((string) $row['nik']),
            'kk' => $this->resolveKk($row),
            'name' => $row['name'] ?? null,
            'date_of_birth' => $this->parseDate($row['tanggal_lahir'] ?? null),
            'gender' => $row['jenis_kelamin'] ?? null,
            'rt' => $row['rt'] ?? null,
            'rw' => $row['rw'] ?? null,
            'hamlet' => $row['dusun'] ?? null,
            'address' => $row['alamat'] ?? null,
            'location_type' => $this->parseLocationType($row['tipe_lokasi'] ?? null),
            'status' => $row['status'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Hapus semua karakter non-angka dari string (untuk NIK/KK).
     */
    private function cleanNumeric(string $value): string
    {
        return preg_replace('/[^0-9]/', '', $value);
    }

    /**
     * Ambil nilai KK dari berbagai kemungkinan nama kolom.
     */
    private function resolveKk(array $row): ?string
    {
        $kk = $row['kk'] ?? $row['nomor_kk'] ?? $row['no_kk'] ?? null;

        return $kk !== null ? $this->cleanNumeric((string) $kk) : null;
    }

    /**
     * Parse tanggal dari format d-m-Y atau d|m|Y.
     */
    private function parseDate(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            $clean = str_replace('|', '-', $value);
            return Carbon::createFromFormat('d-m-Y', $clean)->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning('CivilImport: gagal parse tanggal lahir', [
                'value' => $value,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Normalisasi nilai location_type (kampung → village, selainnya → housing).
     */
    private function parseLocationType(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        return strtolower($value) === 'kampung' ? 'village' : 'housing';
    }
}
