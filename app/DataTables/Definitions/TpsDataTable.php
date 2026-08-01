<?php

namespace App\DataTables\Definitions;

use App\DataTables\Actions\BulkAction;
use App\DataTables\Actions\RowAction;
use App\DataTables\Actions\ToolbarAction;
use App\DataTables\Columns\ActionColumn;
use App\DataTables\Columns\DateColumn;
use App\DataTables\Columns\TextColumn;
use App\DataTables\Contracts\DataTableDefinition;
use App\DataTables\Filters\TextFilter;
use App\Models\Tps;
use Illuminate\Database\Eloquent\Builder;

class TpsDataTable implements DataTableDefinition
{
    public function query(): Builder
    {
        return Tps::query()->withCount('quickCounts');
    }

    public function columns(): array
    {
        return [
            TextColumn::make('code')
                ->label('Kode TPS')
                ->sortable(),

            TextColumn::make('name')
                ->label('Nama TPS')
                ->sortable(),

            TextColumn::make('location')
                ->label('Lokasi TPS'),

            TextColumn::make('total_voters')
                ->label('Total DPT / Pemilih')
                ->sortable(),

            TextColumn::make('quick_counts_count')
                ->label('Total Data Count')
                ->computed(fn ($row) => $row->quick_counts_count ?? 0),

            DateColumn::make('updated_at')
                ->label('Diubah Pada')
                ->format('d M Y H:i')
                ->sortable(),

            ActionColumn::make(),
        ];
    }

    public function filters(): array
    {
        return [
            TextFilter::make('name')->label('Nama TPS'),
            TextFilter::make('code')->label('Kode TPS'),
            TextFilter::make('location')->label('Lokasi'),
        ];
    }

    public function searchableColumns(): array
    {
        return ['name', 'code', 'location'];
    }

    public function actions(): array
    {
        return [
            RowAction::make('edit')
                ->label('Edit')
                ->icon('edit')
                ->emitEvent('open-edit-tps-modal')
                ->requiresPermission('tps.update'),

            RowAction::make('delete')
                ->label('Hapus')
                ->icon('delete')
                ->method('DELETE')
                ->confirmMessage('Yakin ingin menghapus TPS ini?')
                ->requiresPermission('tps.delete'),
        ];
    }

    public function bulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Hapus Terpilih')
                ->endpoint('/settings/tps/delete-bulk')
                ->confirmMessage('Yakin ingin menghapus TPS yang dipilih?')
                ->requiresPermission('tps.delete'),
        ];
    }

    public function toolbarActions(): array
    {
        return [
            ToolbarAction::make('export')
                ->label('Ekspor')
                ->icon('download')
                ->url('/settings/tps/export')
                ->variant('secondary')
                ->requiresPermission('tps.export'),

            ToolbarAction::make('import')
                ->label('Impor')
                ->icon('upload')
                ->emitEvent('open-import-modal')
                ->variant('primary')
                ->requiresPermission('tps.import'),

            ToolbarAction::make('create')
                ->label('Tambah TPS Baru')
                ->icon('plus')
                ->emitEvent('open-tps-modal')
                ->variant('primary')
                ->requiresPermission('tps.create'),
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
