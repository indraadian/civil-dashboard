<?php

namespace App\Actions\Export;

use App\Models\Civil;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class CivilExporter implements ExporterInterface
{
    public function getHeadings(): array
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

    public function buildQuery(array $filters = []): Builder
    {
        $query = Civil::query()->orderBy('updated_at', 'desc');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['hamlet'])) {
            $query->where('hamlet', $filters['hamlet']);
        }

        if (!empty($filters['rt'])) {
            $query->where('rt', $filters['rt']);
        }

        if (!empty($filters['rw'])) {
            $query->where('rw', $filters['rw']);
        }

        return $query;
    }

    public function mapRow(Model $model): array
    {
        /** @var Civil $civil */
        $civil = $model;

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
}
