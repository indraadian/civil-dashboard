<?php

namespace App\Imports;

/**
 * CivilsImport sekarang hanya berperan sebagai value object/DTO
 * yang mendefinisikan field mapping dan konstanta.
 *
 * Proses import yang sebenarnya dilakukan oleh:
 * - ProcessCivilImportJob (orchestration)
 * - ProcessCivilRowAction (transformasi satu baris)
 *
 * Class ini dipertahankan untuk backward compatibility dan dokumentasi.
 */
class CivilsImport
{
    /**
     * Nama kolom yang diharapkan ada di file Excel/CSV (setelah normalisasi ke snake_case).
     *
     * @var array<int, string>
     */
    public const EXPECTED_COLUMNS = [
        'kk',
        'nik',
        'name',
        'tempat_lahir',
        'tanggal_lahir',
        'jenis_kelamin',
        'rt',
        'rw',
        'dusun',
        'alamat',
        'tipe_lokasi',
        'status',
    ];

    /**
     * Alias kolom yang diterima untuk kolom KK.
     *
     * @var array<int, string>
     */
    public const KK_COLUMN_ALIASES = ['kk', 'nomor_kk', 'no_kk'];

    /**
     * Alias kolom yang diterima untuk kolom Tempat Lahir.
     *
     * @var array<int, string>
     */
    public const POB_COLUMN_ALIASES = ['tempat_lahir', 'pob', 'place_of_birth'];

    /**
     * Jumlah baris yang diproses per chunk (batch).
     */
    public const CHUNK_SIZE = 1000;

    /**
     * Validasi apakah heading dari file sesuai dengan yang diharapkan.
     *
     * @param  array<int, string>  $headings
     * @return array<int, string> Daftar kolom yang hilang
     */
    public static function getMissingColumns(array $headings): array
    {
        $required = array_diff(self::EXPECTED_COLUMNS, ['kk']); // KK boleh kosong/alias

        return array_values(array_diff($required, $headings));
    }
}
