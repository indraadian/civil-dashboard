<?php

namespace App\DataTables\Definitions;

use App\DataTables\Actions\ToolbarAction;
use App\DataTables\Columns\DateColumn;
use App\DataTables\Columns\TextColumn;
use App\DataTables\Contracts\DataTableDefinition;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Permission;

class PermissionDataTable implements DataTableDefinition
{
    public function query(): Builder
    {
        return Permission::query();
    }

    public function columns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Kode Permission')
                ->sortable(),

            TextColumn::make('module')
                ->label('Modul')
                ->computed(function ($row) {
                    $parts = explode('.', $row->name);
                    return count($parts) > 1 ? strtoupper($parts[0]) : 'SYSTEM';
                }),

            TextColumn::make('action')
                ->label('Aksi / Fitur')
                ->computed(function ($row) {
                    $parts = explode('.', $row->name);
                    return count($parts) > 1 ? ucfirst($parts[1]) : $row->name;
                }),

            DateColumn::make('created_at')
                ->label('Dibuat Pada')
                ->format('d M Y H:i')
                ->sortable(),
        ];
    }

    public function filters(): array
    {
        return [];
    }

    public function searchableColumns(): array
    {
        return ['name'];
    }

    public function actions(): array
    {
        return [];
    }

    public function bulkActions(): array
    {
        return [];
    }

    public function toolbarActions(): array
    {
        return [
            ToolbarAction::make('sync')
                ->label('Sync Permission')
                ->icon('upload')
                ->url('/settings/permissions/sync')
                ->method('POST')
                ->variant('primary')
                ->requiresPermission('permission.sync'),
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
        return 'name';
    }

    public function defaultSortDirection(): string
    {
        return 'asc';
    }
}
