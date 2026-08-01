<?php

namespace App\DataTables\Definitions;

use App\DataTables\Actions\BulkAction;
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
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class QuickCountDataTable implements DataTableDefinition
{
    public function query(): Builder
    {
        return QuickCount::query()
            ->with(['tps', 'creator', 'updater'])
            ->forUser(auth()->user());
    }

    public function columns(): array
    {
        return [
            TextColumn::make('tps.name')
                ->label('TPS')
                ->sortable(),

            TextColumn::make('vote_count')
                ->label('Perolehan Suara')
                ->sortable(),

            TextColumn::make('total_voters')
                ->label('Total Pemilih TPS')
                ->sortable(),

            TextColumn::make('progress')
                ->label('Persentase Suara')
                ->computed(function ($row) {
                    $votes = $row->vote_count ?? 0;
                    $total = $row->total_voters ?? 0;
                    $percent = $total > 0 ? round(($votes / $total) * 100, 1) : 0;

                    return "{$percent}% ({$votes} dari {$total})";
                }),

            ImageColumn::make('c1_photo')
                ->label('Foto C1')
                ->computed(fn ($row) => $row->c1_photo_url),

            TextColumn::make('creator.name')
                ->label('Petugas Input')
                ->computed(fn ($row) => $row->creator?->name ?? '-'),

            DateColumn::make('updated_at')
                ->label('Diubah Pada')
                ->format('d M Y H:i')
                ->sortable(),

            ActionColumn::make(),
        ];
    }

    public function filters(): array
    {
        $tpsOptions = Tps::pluck('name', 'id')->toArray();
        $userOptions = User::pluck('name', 'id')->toArray();

        return [
            SelectFilter::make('tps_id')->label('TPS')->options($tpsOptions),
            SelectFilter::make('created_by')->label('Petugas Input')->options($userOptions),
            TextFilter::make('notes')->label('Catatan'),
        ];
    }

    public function searchableColumns(): array
    {
        return ['notes'];
    }

    public function actions(): array
    {
        return [
            RowAction::make('edit')
                ->label('Edit')
                ->icon('edit')
                ->emitEvent('open-edit-quick-count-modal'),

            RowAction::make('delete')
                ->label('Hapus')
                ->icon('delete')
                ->method('DELETE')
                ->confirmMessage('Yakin ingin menghapus data Quick Count ini?'),
        ];
    }

    public function bulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Hapus Terpilih')
                ->endpoint('/quick-counts/delete-bulk')
                ->confirmMessage('Yakin ingin menghapus data Quick Count terpilih?'),
        ];
    }

    public function toolbarActions(): array
    {
        return [
            ToolbarAction::make('export')
                ->label('Ekspor')
                ->icon('download')
                ->url('/quick-counts/export')
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
                ->label('Input Quick Count')
                ->icon('plus')
                ->emitEvent('open-quick-count-modal')
                ->variant('primary'),
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
