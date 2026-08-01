<?php

namespace App\DataTables\Definitions;

use App\DataTables\Actions\BulkAction;
use App\DataTables\Actions\RowAction;
use App\DataTables\Actions\ToolbarAction;
use App\DataTables\Columns\ActionColumn;
use App\DataTables\Columns\BadgeColumn;
use App\DataTables\Columns\TextColumn;
use App\DataTables\Contracts\DataTableDefinition;
use App\DataTables\Filters\SelectFilter;
use App\DataTables\Filters\TextFilter;
use App\Models\Rw;
use Illuminate\Database\Eloquent\Builder;

class RwDataTable implements DataTableDefinition
{
    public function query(): Builder
    {
        return Rw::query()->withCount('rts');
    }

    public function columns(): array
    {
        return [
            TextColumn::make('code')
                ->label('Kode RW')
                ->prefix('RW ')
                ->sortable(),

            TextColumn::make('name')
                ->label('Nama RW')
                ->sortable(),

            TextColumn::make('rts_count')
                ->label('Jumlah RT')
                ->computed(fn($rw) => ($rw->rts_count ?? 0) . ' RT'),

            BadgeColumn::make('is_active')
                ->label('Status')
                ->mapping([
                    1     => ['label' => 'Aktif', 'color' => 'success'],
                    0     => ['label' => 'Non-Aktif', 'color' => 'error'],
                    true  => ['label' => 'Aktif', 'color' => 'success'],
                    false => ['label' => 'Non-Aktif', 'color' => 'error'],
                ])
                ->sortable(),

            ActionColumn::make(),
        ];
    }

    public function filters(): array
    {
        return [
            TextFilter::make('code')->label('Kode RW'),
            TextFilter::make('name')->label('Nama RW'),
            SelectFilter::make('is_active')
                ->label('Status')
                ->options([
                    '1' => 'Aktif',
                    '0' => 'Non-Aktif',
                ]),
        ];
    }

    public function searchableColumns(): array
    {
        return ['code', 'name'];
    }

    public function actions(): array
    {
        return [
            RowAction::make('edit')
                ->label('Edit')
                ->icon('edit')
                ->emitEvent('open-edit-rw-modal'),

            RowAction::make('delete')
                ->label('Hapus')
                ->icon('delete')
                ->method('DELETE')
                ->confirmMessage('Yakin ingin menghapus RW ini?'),
        ];
    }

    public function bulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Hapus')
                ->endpoint('/settings/rws/delete-bulk')
                ->confirmMessage('Yakin ingin menghapus data RW terpilih?'),
        ];
    }

    public function toolbarActions(): array
    {
        return [
            ToolbarAction::make('export')
                ->label('Ekspor')
                ->icon('download')
                ->url('/settings/rws/export')
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
                ->label('Tambah RW')
                ->icon('plus')
                ->emitEvent('open-rw-modal')
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
        return 'code';
    }

    public function defaultSortDirection(): string
    {
        return 'asc';
    }
}
