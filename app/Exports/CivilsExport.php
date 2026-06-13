<?php

namespace App\Exports;

use App\Models\Civil;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;

class CivilsExport implements FromQuery, WithMapping, WithHeadings
{
    use Exportable;

    /**
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query()
    {
        // PENTING: Jangan gunakan ->get() di sini!
        // Kembalikan objek query-nya saja agar library bisa melakukan chunking.
        return Civil::query()
            ->orderBy('updated_at', 'desc')
            ->select([
                'nik',
                'name',
                'date_of_birth',
                'gender',
                'hamlet',
                'location_type',
                'rt',
                'rw',
                'address',
                'status'
            ]);
    }

    // /**
    //  * @return \Illuminate\Support\Collection
    //  */
    // public function collection()
    // {
    //     $columns = [
    //         'nik',
    //         'name',
    //         'date_of_birth',
    //         'gender',
    //         'hamlet',
    //         'location_type',
    //         'rt',
    //         'rw',
    //         'address',
    //         'status'
    //     ];
    //     return Civil::orderBy('updated_at', 'desc')->select($columns)->get();
    // }

    public function headings(): array
    {
        return [
            'NIK',
            'Nama Lengkap',
            'Tanggal Lahir',
            'Usia',
            'Jenis Kelamin',
            'RT',
            'RW',
            'Dusun',
            'Alamat',
            'Jenis Lokasi',
            'Status'
        ];
    }

    public function map($user): array
    {
        $age = $user->date_of_birth
            ? (date('Y') - \Carbon\Carbon::parse($user->date_of_birth)->year)
            : '-';
        return [
            "'" . $user->nik,
            $user->name,
            $user->date_of_birth,
            $age,
            $user->gender ?? '-',
            "'" . $user->rt, // Menjaga format 001, 002
            "'" . $user->rw, // Menjaga format 001, 002
            $user->hamlet,
            $user->address,
            $user->location_type === 'village' ? 'Kampung' : 'Perumahan',
            $user->status
        ];
    }
}
