<?php

namespace App\Services;

use Spatie\Permission\Models\Permission;

class PermissionSyncService
{
    /**
     * Get module definitions and their associated permissions.
     *
     * @return array<string, array<int, string>>
     */
    public static function getModulePermissions(): array
    {
        return [
            'Civil' => [
                'civil.view'   => 'View Penduduk',
                'civil.create' => 'Create Penduduk',
                'civil.update' => 'Update Penduduk',
                'civil.delete' => 'Delete Penduduk',
                'civil.import' => 'Import Penduduk',
                'civil.export' => 'Export Penduduk',
            ],
            'User' => [
                'user.view'   => 'View User',
                'user.create' => 'Create User',
                'user.update' => 'Update User',
                'user.delete' => 'Delete User',
                'user.import' => 'Import User',
                'user.export' => 'Export User',
            ],
            'TPS' => [
                'tps.view'   => 'View TPS',
                'tps.create' => 'Create TPS',
                'tps.update' => 'Update TPS',
                'tps.delete' => 'Delete TPS',
                'tps.import' => 'Import TPS',
                'tps.export' => 'Export TPS',
            ],
            'RW' => [
                'rw.view'   => 'View RW',
                'rw.create' => 'Create RW',
                'rw.update' => 'Update RW',
                'rw.delete' => 'Delete RW',
                'rw.import' => 'Import RW',
                'rw.export' => 'Export RW',
            ],
            'RT' => [
                'rt.view'   => 'View RT',
                'rt.create' => 'Create RT',
                'rt.update' => 'Update RT',
                'rt.delete' => 'Delete RT',
                'rt.import' => 'Import RT',
                'rt.export' => 'Export RT',
            ],
            'Quick Count' => [
                'quick-count.view'   => 'View Quick Count',
                'quick-count.create' => 'Create Quick Count',
                'quick-count.update' => 'Update Quick Count',
                'quick-count.delete' => 'Delete Quick Count',
                'quick-count.import' => 'Import Quick Count',
                'quick-count.export' => 'Export Quick Count',
            ],
            'Role' => [
                'role.view'   => 'View Role',
                'role.create' => 'Create Role',
                'role.update' => 'Update Role',
                'role.delete' => 'Delete Role',
            ],
            'Permission' => [
                'permission.view' => 'View Permission',
                'permission.sync' => 'Sync Permission',
            ],
            'System' => [
                'dashboard.view' => 'View Dashboard',
                'migration.run'  => 'Run Migration & Maintenance',
            ],
        ];
    }

    /**
     * Synchronize permissions with the database.
     */
    public function sync(): int
    {
        $count = 0;
        $allPermissions = self::getModulePermissions();

        foreach ($allPermissions as $module => $permissions) {
            foreach ($permissions as $name => $label) {
                Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web']
                );
                $count++;
            }
        }

        return $count;
    }
}
