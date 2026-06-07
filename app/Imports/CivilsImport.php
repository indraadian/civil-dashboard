<?php

namespace App\Imports;

use App\Models\Civil;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class CivilsImport implements ToModel, WithHeadingRow, WithSkipDuplicates, SkipsEmptyRows
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Cek apakah NIK ada, jika tidak, skip baris ini
        if (empty($row['nik'])) {
            return null;
        }

        $cleanNik = preg_replace('/[^0-9]/', '', (string)$row['nik']);

        $formattedDate = null;
        if (!empty($row['tanggal_lahir'])) {
            $cleanDate = str_replace('|', '-', $row['tanggal_lahir']);
            try {
                $formattedDate = Carbon::createFromFormat('d-m-Y', $cleanDate)->format('Y-m-d');
            } catch (\Exception $e) {
                $formattedDate = null;
            }
        }

        $locationType = null;
        if (!empty($row['tipe_lokasi'])) {
            $locationType = $row['tipe_lokasi'];
            $locationType = $locationType == "kampung" ? "village" : "housing"; // Ubah ke huruf kecil untuk konsistensi
        }

        return new Civil([
            'nik'           => $cleanNik, // Harus sesuai teks header (abaikan case sensitive)
            'name'          => $row['name'],
            'date_of_birth' => $formattedDate, // Gunakan tanggal yang sudah diformat
            'gender'        => $row['jenis_kelamin'], // Excel: "Jenis Kelamin" -> jadi "jenis_kelamin"
            'rt'            => $row['rt'],
            'rw'            => $row['rw'],
            'hamlet'        => $row['dusun'],         // Excel: "Dusun"
            'address'       => $row['alamat'],        // Excel: "Alamat"
            'location_type' => $locationType,
            'status'        => $row['status'],
            'created_at'    => now(),
            'updated_at'    => now()
        ]);
    }

    public function chunkSize(): int
    {
        return 1000; // Memproses 1000 baris per antrian
    }


    public function startRow(): int
    {
        return 2;
    }
}
