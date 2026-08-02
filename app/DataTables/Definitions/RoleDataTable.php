<?php

namespace App\DataTables\Definitions;

use App\DataTables\Actions\RowAction;
use App\DataTables\Actions\ToolbarAction;
use App\DataTables\Columns\ActionColumn;
use App\DataTables\Columns\DateColumn;
use App\DataTables\Columns\TextColumn;
use App\DataTables\Contracts\DataTableDefinition;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class RoleDataTable implements DataTableDefinition
{
    public function query(): Builder
    {
        return Role::query()
            ->where('id', '!=', '1')
            ->withCount(['users', 'permissions']);
    }

    public function columns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Nama Role')
                ->sortable(),

            TextColumn::make('users_count')
                ->label('Jumlah User')
                ->computed(fn($row) => $row->users_count ?? 0),

            TextColumn::make('permissions_count')
                ->label('Jumlah Permission')
                ->computed(fn($row) => $row->name === 'Super Admin' ? 'Semua (Bypass)' : ($row->permissions_count ?? 0)),

            DateColumn::make('created_at')
                ->label('Dibuat Pada')
                ->format('d M Y H:i')
                ->sortable(),

            DateColumn::make('updated_at')
                ->label('Diubah Pada')
                ->format('d M Y H:i')
                ->sortable(),

            ActionColumn::make(),
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
        return [
            RowAction::make('edit')
                ->label('Edit')
                ->icon('edit')
                ->emitEvent('open-edit-role-modal')
                ->requiresPermission('role.update'),

            RowAction::make('delete')
                ->label('Hapus')
                ->icon('delete')
                ->method('DELETE')
                ->confirmMessage('Yakin ingin menghapus Role ini?')
                ->requiresPermission('role.delete'),
        ];
    }

    public function bulkActions(): array
    {
        return [];
    }

    public function toolbarActions(): array
    {
        return [
            ToolbarAction::make('create')
                ->label('Tambah Role Baru')
                ->icon('plus')
                ->emitEvent('open-role-modal')
                ->variant('primary')
                ->requiresPermission('role.create'),

            ToolbarAction::make('sync-roles-permissions')
                ->label('Sync Role & Permission')
                ->icon('upload')
                ->url('/settings/sync-roles-permissions')
                ->method('POST')
                ->variant('secondary')
                ->requiresPermission('permission.sync'),
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
        return 'name';
    }

    public function defaultSortDirection(): string
    {
        return 'asc';
    }
}
