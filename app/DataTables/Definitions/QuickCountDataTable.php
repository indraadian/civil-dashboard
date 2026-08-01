<?php

namespace App\DataTables\Definitions;

use App\DataTables\Actions\RowAction;
use App\DataTables\Actions\ToolbarAction;
use App\DataTables\Columns\ActionColumn;
use App\DataTables\Columns\DateColumn;
use App\DataTables\Columns\ImageColumn;
use App\DataTables\Columns\TextColumn;
use App\DataTables\Contracts\DataTableDefinition;
use App\DataTables\Filters\SelectFilter;
use App\DataTables\Filters\TextFilter;
use App\Models\QuickCount;
use App\Models\Tps;
use Illuminate\Database\Eloquent\Builder;

class QuickCountDataTable implements DataTableDefinition
{
    public function query(): Builder
    {
        return QuickCount::query()
            ->with(['tps', 'details.candidate'])
            ->forCurrentUser();
    }

    public function columns(): array
    {
        return [
            TextColumn::make('tps.name')
                ->label('TPS'),

            TextColumn::make('officer_name')
                ->label('Nama Petugas')
                ->sortable(),

            TextColumn::make('officer_phone')
                ->label('No. HP')
                ->sortable(),

            DateColumn::make('input_at')
                ->label('Waktu Input')
                ->format('d M Y H:i')
                ->sortable(),

            TextColumn::make('valid_votes')
                ->label('Suara Sah')
                ->computed(fn($row) => number_format($row->details->sum('vote_count'), 0, ',', '.')),

            TextColumn::make('invalid_votes')
                ->label('Tidak Sah')
                ->computed(fn($row) => number_format($row->invalid_votes, 0, ',', '.')),

            TextColumn::make('total_voters')
                ->label('Total Pengguna')
                ->computed(fn($row) => number_format($row->total_voters, 0, ',', '.')),

            ImageColumn::make('c1_photo_url')
                ->label('Foto C1'),

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
            SelectFilter::make('tps_id')
                ->label('TPS')
                ->options(Tps::orderBy('name')->pluck('name', 'id')->toArray()),

            TextFilter::make('officer_name')
                ->label('Nama Petugas'),
        ];
    }

    public function searchableColumns(): array
    {
        return ['officer_name', 'officer_phone', 'tps.name'];
    }

    public function actions(): array
    {
        return [
            RowAction::make('edit')
                ->label('Edit')
                ->icon('edit')
                ->emitEvent('open-edit-quick-count-modal')
                ->requiresPermission('quick-count.update'),

            RowAction::make('delete')
                ->label('Hapus')
                ->icon('delete')
                ->method('DELETE')
                ->confirmMessage('Yakin ingin menghapus data Quick Count ini?')
                ->requiresPermission('quick-count.delete'),
        ];
    }

    public function bulkActions(): array
    {
        return [];
    }

    public function toolbarActions(): array
    {
        return [
            ToolbarAction::make('export')
                ->label('Ekspor')
                ->icon('download')
                ->emitEvent('open-export-modal')
                ->variant('secondary')
                ->requiresPermission('quick-count.export'),

            ToolbarAction::make('import')
                ->label('Impor')
                ->icon('upload')
                ->emitEvent('open-import-modal')
                ->variant('secondary')
                ->requiresPermission('quick-count.import'),

            ToolbarAction::make('create')
                ->label('Input Quick Count')
                ->icon('plus')
                ->emitEvent('open-quick-count-modal')
                ->variant('primary')
                ->requiresPermission('quick-count.create'),
        ];
    }

    public function perPageOptions(): array
    {
        return [10, 25, 50, 100];
    }

    public function defaultPerPage(): int
    {
        return 25;
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
