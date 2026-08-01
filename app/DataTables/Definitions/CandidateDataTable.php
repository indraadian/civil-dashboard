<?php

namespace App\DataTables\Definitions;

use App\DataTables\Actions\BulkAction;
use App\DataTables\Actions\RowAction;
use App\DataTables\Actions\ToolbarAction;
use App\DataTables\Columns\ActionColumn;
use App\DataTables\Columns\BadgeColumn;
use App\DataTables\Columns\DateColumn;
use App\DataTables\Columns\ImageColumn;
use App\DataTables\Columns\TextColumn;
use App\DataTables\Contracts\DataTableDefinition;
use App\DataTables\Filters\SelectFilter;
use App\Models\Candidate;
use Illuminate\Database\Eloquent\Builder;

class CandidateDataTable implements DataTableDefinition
{
    public function query(): Builder
    {
        return Candidate::query();
    }

    public function columns(): array
    {
        return [
            TextColumn::make('number')
                ->label('No. Urut')
                ->sortable(),

            TextColumn::make('name')
                ->label('Nama Pasangan Calon')
                ->sortable(),

            ImageColumn::make('photo_url')
                ->label('Foto')
                ->defaultImage('/images/default-avatar.png'),

            BadgeColumn::make('is_active')
                ->label('Status')
                ->mapping([
                    1 => ['label' => 'Aktif', 'color' => 'success'],
                    0 => ['label' => 'Nonaktif', 'color' => 'danger'],
                ]),

            DateColumn::make('created_at')
                ->label('Dibuat Pada')
                ->format('d M Y H:i')
                ->sortable(),

            ActionColumn::make(),
        ];
    }

    public function filters(): array
    {
        return [
            SelectFilter::make('is_active')
                ->label('Status Aktif')
                ->options([
                    '1' => 'Aktif',
                    '0' => 'Nonaktif',
                ]),
        ];
    }

    public function searchableColumns(): array
    {
        return ['name', 'number'];
    }

    public function actions(): array
    {
        return [
            RowAction::make('edit')
                ->label('Edit')
                ->icon('edit')
                ->emitEvent('open-edit-candidate-modal')
                ->requiresPermission('candidate.update'),

            RowAction::make('delete')
                ->label('Hapus')
                ->icon('delete')
                ->method('DELETE')
                ->confirmMessage('Yakin ingin menghapus calon ini?')
                ->requiresPermission('candidate.delete'),
        ];
    }

    public function bulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Hapus')
                ->endpoint('/settings/candidates/delete-bulk')
                ->confirmMessage('Yakin ingin menghapus data Candidate yang dipilih?')
                ->requiresPermission('candidate.delete'),
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
                ->requiresPermission('candidate.view'),

            ToolbarAction::make('import')
                ->label('Impor')
                ->icon('upload')
                ->emitEvent('open-import-modal')
                ->variant('secondary')
                ->requiresPermission('candidate.create'),

            ToolbarAction::make('create')
                ->label('Tambah Candidate')
                ->icon('plus')
                ->emitEvent('open-candidate-modal')
                ->variant('primary')
                ->requiresPermission('candidate.create'),
        ];
    }

    public function perPageOptions(): array
    {
        return [10, 25, 50];
    }

    public function defaultPerPage(): int
    {
        return 10;
    }

    public function defaultSortField(): string
    {
        return 'number';
    }

    public function defaultSortDirection(): string
    {
        return 'asc';
    }
}
