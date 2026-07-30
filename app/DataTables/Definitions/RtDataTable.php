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
use App\Models\Rt;
use App\Models\Rw;
use Illuminate\Database\Eloquent\Builder;

class RtDataTable implements DataTableDefinition
{
    public function query(): Builder
    {
        return Rt::query()->with('rw');
    }

    public function columns(): array
    {
        return [
            TextColumn::make('code')
                ->label('Kode RT')
                ->prefix('RT ')
                ->sortable(),

            TextColumn::make('name')
                ->label('Nama RT')
                ->sortable(),

            TextColumn::make('rw_name')
                ->label('Induk RW')
                ->computed(fn($rt) => $rt->rw ? "RW {$rt->rw->code} ({$rt->rw->name})" : '-'),

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
        $rwOptions = Rw::orderBy('code', 'asc')
            ->get()
            ->mapWithKeys(fn($rw) => [$rw->id => "RW {$rw->code} - {$rw->name}"])
            ->toArray();

        return [
            TextFilter::make('code')->label('Kode RT'),
            TextFilter::make('name')->label('Nama RT'),
            SelectFilter::make('rw_id')
                ->label('Filter RW')
                ->options($rwOptions),
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
                ->emitEvent('open-edit-rt-modal'),

            RowAction::make('delete')
                ->label('Hapus')
                ->icon('delete')
                ->method('DELETE')
                ->confirmMessage('Yakin ingin menghapus RT ini?'),
        ];
    }

    public function bulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Hapus')
                ->endpoint('/settings/rts/delete-bulk')
                ->confirmMessage('Yakin ingin menghapus data RT terpilih?'),
        ];
    }

    public function toolbarActions(): array
    {
        return [
            ToolbarAction::make('create')
                ->label('Tambah RT')
                ->icon('plus')
                ->emitEvent('open-rt-modal')
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
        return 'code';
    }

    public function defaultSortDirection(): string
    {
        return 'asc';
    }
}
