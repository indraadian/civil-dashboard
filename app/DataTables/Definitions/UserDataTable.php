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
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserDataTable implements DataTableDefinition
{
    public function query(): Builder
    {
        return User::query()
            ->where('role', '!=', 'super_admin')
            ->with(['locationScopes.rw', 'locationScopes.rt']);
    }

    public function columns(): array
    {
        return [
            AvatarColumn::make('name')
                ->label('Pengguna')
                ->initials(fn($user) => strtoupper(substr($user->name, 0, 2)))
                ->colorBy('role', [
                    'admin' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
                    'rw' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
                    'rt' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600'],
                    'user' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600'],
                ])
                ->sortable(),

            TextColumn::make('email')
                ->label('Email')
                ->sortable(),

            BadgeColumn::make('role')
                ->label('Role')
                ->mapping([
                    'admin' => ['label' => 'Admin', 'color' => 'primary'],
                    'rw' => ['label' => 'Pengurus RW', 'color' => 'success'],
                    'rt' => ['label' => 'Pengurus RT', 'color' => 'warning'],
                    'user' => ['label' => 'User', 'color' => 'secondary'],
                    'super_admin' => ['label' => 'Super Admin', 'color' => 'error'],
                ])
                ->sortable(),

            TextColumn::make('scopes')
                ->label('Hak Akses Wilayah')
                ->computed(function ($user) {
                    if ($user->role === 'admin') {
                        return 'Semua Wilayah';
                    }

                    if ($user->locationScopes->isEmpty()) {
                        return 'Tidak Ada Scope';
                    }

                    return $user->locationScopes->map(function ($s) {
                        if ($s->rt) {
                            return "RW {$s->rw?->code} / RT {$s->rt?->code}";
                        }
                        return "RW {$s->rw?->code} (Semua RT)";
                    })->implode(', ');
                }),

            DateColumn::make('created_at')
                ->label('Terdaftar')
                ->format('d M Y H:i')
                ->sortable(),

            ActionColumn::make(),
        ];
    }

    public function filters(): array
    {
        return [
            TextFilter::make('name')->label('Nama'),
            TextFilter::make('email')->label('Email'),
            SelectFilter::make('role')
                ->label('Role')
                ->options([
                    'admin' => 'Admin',
                    'rw' => 'Pengurus RW',
                    'rt' => 'Pengurus RT',
                    'user' => 'User',
                ]),
        ];
    }

    public function searchableColumns(): array
    {
        return ['name', 'email', 'role'];
    }

    public function actions(): array
    {
        return [
            RowAction::make('edit')
                ->label('Edit')
                ->icon('edit')
                ->emitEvent('open-edit-user-modal')
                ->requiresPermission('user.update'),

            RowAction::make('delete')
                ->label('Hapus')
                ->icon('delete')
                ->method('DELETE')
                ->confirmMessage('Yakin ingin menghapus user ini?')
                ->requiresPermission('user.delete'),
        ];
    }

    public function bulkActions(): array
    {
        return [
            BulkAction::make('delete')
                ->label('Hapus')
                ->endpoint('/settings/users/delete-bulk')
                ->confirmMessage('Yakin ingin menghapus data user terpilih?')
                ->requiresPermission('user.delete'),
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
                ->requiresPermission('user.export'),

            ToolbarAction::make('import')
                ->label('Impor')
                ->icon('upload')
                ->emitEvent('open-import-modal')
                ->variant('secondary')
                ->requiresPermission('user.import'),

            ToolbarAction::make('create')
                ->label('Tambah User')
                ->icon('plus')
                ->emitEvent('open-user-modal')
                ->variant('primary')
                ->requiresPermission('user.create'),
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
        return 'created_at';
    }

    public function defaultSortDirection(): string
    {
        return 'desc';
    }
}
