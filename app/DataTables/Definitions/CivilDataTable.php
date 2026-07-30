<?php

namespace App\DataTables\Definitions;

use App\DataTables\Actions\BulkAction;
use App\DataTables\Actions\RowAction;
use App\DataTables\Actions\ToolbarAction;
use App\DataTables\Columns\ActionColumn;
use App\DataTables\Columns\AvatarColumn;
use App\DataTables\Columns\BadgeColumn;
use App\DataTables\Columns\DateColumn;
use App\DataTables\Columns\TextColumn;
use App\DataTables\Contracts\DataTableDefinition;
use App\DataTables\Filters\SelectFilter;
use App\DataTables\Filters\TextFilter;
use App\Models\Civil;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

class CivilDataTable implements DataTableDefinition
{
    public function query(): Builder
    {
        return Civil::query()->forUser(auth()->user());
    }

    public function columns(): array
    {
        return [
            TextColumn::make('kk')
                ->label('No. KK'),

            TextColumn::make('nik')
                ->label('NIK')
                ->sortable(),

            AvatarColumn::make('name')
                ->label('Nama')
                ->initials(fn($row) => strtoupper(substr($row->name, 0, 2)))
                ->colorBy('location_type', [
                    'housing' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-500'],
                    'village' => ['bg' => 'bg-green-50', 'text' => 'text-green-600'],
                ])
                ->sortable(),

            TextColumn::make('place_of_birth')
                ->label('Tempat Lahir'),

            DateColumn::make('date_of_birth')
                ->label('Tanggal Lahir')
                ->format('d F Y')
                ->locale('id-ID')
                ->sortable(),

            TextColumn::make('age')
                ->label('Usia')
                ->computed(fn($row) => $row->date_of_birth
                    ? Carbon::parse($row->date_of_birth)->age
                    : '-'),

            TextColumn::make('gender')
                ->label('Jenis Kelamin'),

            TextColumn::make('rt')
                ->label('RT')
                ->prefix('RT '),

            TextColumn::make('rw')
                ->label('RW')
                ->prefix('RW '),

            TextColumn::make('hamlet')
                ->label('Dusun'),

            TextColumn::make('address')
                ->label('Alamat')
                ->visible(false),

            BadgeColumn::make('location_type')
                ->label('Tipe Lokasi')
                ->mapping([
                    'housing' => ['label' => 'Perumahan', 'color' => 'primary'],
                    'village' => ['label' => 'Kampung', 'color' => 'success'],
                ]),

            BadgeColumn::make('status')
                ->label('Status')
                ->mapping([
                    'Militan' => ['label' => 'Militan', 'color' => 'success'],
                    'Ngambang' => ['label' => 'Ngambang', 'color' => 'primary'],
                    'Lawan' => ['label' => 'Lawan', 'color' => 'error'],
                ]),

            ActionColumn::make()
        ];
    }

    public function filters(): array
    {
        return [
            TextFilter::make('name')->label('Nama'),
            TextFilter::make('nik')->label('NIK')->operator('equals'),
            TextFilter::make('kk')->label('KK')->operator('equals'),
            TextFilter::make('address')->label('Alamat'),
            SelectFilter::make('status')->label('Status')
                ->options(['Militan', 'Ngambang', 'Lawan']),
            SelectFilter::make('location_type')->label('Tipe Lokasi')
                ->options(['village' => 'Kampung', 'housing' => 'Perumahan']),
            SelectFilter::make('gender')->label('Jenis Kelamin')
                ->options(['L' => 'Laki-Laki', 'P' => 'Perempuan']),
            TextFilter::make('rt')->label('RT')->operator('equals'),
            TextFilter::make('rw')->label('RW')->operator('equals'),
            TextFilter::make('hamlet')->label('Dusun'),
        ];
    }

    public function searchableColumns(): array
    {
        return ['name', 'nik', 'kk', 'place_of_birth'];
    }

    public function actions(): array
    {
        return [
            RowAction::make('edit')
                ->label('Edit')
                ->icon('edit')
                ->emitEvent('open-edit-civil-modal'),

            RowAction::make('delete')
                ->label('Hapus')
                ->icon('delete')
                ->method('DELETE')
                ->confirmMessage('Yakin ingin menghapus data ini?')
                ->requiresRole('admin')
                ->requiresRole('super_admin'),
        ];
    }

    public function bulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Hapus')
                ->endpoint('/civils/delete-bulk')
                ->confirmMessage('Yakin ingin menghapus data yang dipilih?')
                ->requiresRole('admin')
                ->requiresRole('super_admin'),
        ];
    }

    public function toolbarActions(): array
    {
        return [
            ToolbarAction::make('export')
                ->label('Ekspor')
                ->icon('download')
                ->emitEvent('open-export-modal')
                ->variant('secondary')
                ->requiresRole('admin')
                ->requiresRole('super_admin'),

            ToolbarAction::make('import')
                ->label('Impor')
                ->icon('upload')
                ->emitEvent('open-import-modal')
                ->variant('primary')
                ->requiresRole('admin')
                ->requiresRole('super_admin'),

            ToolbarAction::make('create')
                ->label('Tambah')
                ->icon('plus')
                ->emitEvent('open-civil-modal')
                ->variant('primary')
                ->requiresRole('admin')
                ->requiresRole('super_admin'),
        ];
    }

    public function perPageOptions(): array
    {
        return [10, 25, 50, 100];
    }

    public function defaultPerPage(): int
    {
        return 10;
    }

    public function defaultSortField(): string
    {
        return 'updated_at';
    }

    public function defaultSortDirection(): string
    {
        return 'desc';
    }
}
