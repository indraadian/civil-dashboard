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
        $nik = $this->resolveNik($row);
        if (empty($nik)) {
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
            $nik = $this->resolveNik($row);
            if (empty($nik)) {
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
        $nik = $this->resolveNik($row);
        $name = $row['name'] ?? $row['nama'] ?? $row['nama_lengkap'] ?? $row['nama_penduduk'] ?? '-';
        $genderRaw = $row['gender'] ?? $row['jenis_kelamin'] ?? $row['jk'] ?? 'L';
        $gender = in_array(strtoupper((string) $genderRaw), ['P', 'PEREMPUAN', 'WOMAN', 'FEMALE']) ? 'P' : 'L';

        return [
            'kk' => $this->resolveKk($row),
            'nik' => $nik,
            'name' => (string) $name,
            'place_of_birth' => $row['tempat_lahir'] ?? $row['place_of_birth'] ?? $row['pob'] ?? null,
            'date_of_birth' => $this->parseDate($row['tanggal_lahir'] ?? $row['date_of_birth'] ?? $row['tgl_lahir'] ?? null),
            'gender' => $gender,
            'rt' => isset($row['rt']) ? sprintf('%03d', (int) $row['rt']) : '001',
            'rw' => isset($row['rw']) ? sprintf('%03d', (int) $row['rw']) : '001',
            'hamlet' => $row['dusun'] ?? $row['hamlet'] ?? null,
            'address' => $row['alamat'] ?? $row['address'] ?? $row['alamat_lengkap'] ?? '-',
            'location_type' => $this->parseLocationType($row['tipe_lokasi'] ?? $row['location_type'] ?? null),
            'status' => $row['status'] ?? 'Ngambang',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    private function resolveNik(array $row): ?string
    {
        $nik = $row['nik'] ?? $row['nomor_nik'] ?? $row['no_nik'] ?? null;
        return $nik !== null ? $this->cleanNumeric((string) $nik) : null;
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
