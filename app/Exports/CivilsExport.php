<?php

namespace App\Exports;

/**
 * CivilsExport sekarang hanya berperan sebagai value object/DTO
 * yang mendefinisikan heading dan field mapping untuk export.
 *
 * Proses export yang sebenarnya dilakukan oleh:
 * - GenerateCivilExportJob (orchestration & file generation)
 *
 * Class ini dipertahankan untuk dokumentasi dan referensi field mapping.
 */
class CivilsExport
{
    /**
     * Heading kolom untuk file export.
     *
     * @return array<int, string>
     */
    public static function headings(): array
    {
        return [
            'NIK',
            'KK',
            'Nama Lengkap',
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
     * Kolom yang akan di-select dari database.
     *
     * @return array<int, string>
     */
    public static function columns(): array
    {
        return [
            'nik',
            'kk',
            'name',
            'date_of_birth',
            'gender',
            'hamlet',
            'location_type',
            'rt',
            'rw',
            'address',
            'status',
        ];
    }
}
