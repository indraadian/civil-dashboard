<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminRole = Role::where('name', 'Admin')->first();
        $userRole = Role::where('name', 'User')->first();

        // Operational permissions for Admin (all except migration.run)
        $adminPermissions = Permission::where('name', '!=', 'migration.run')->get();
        if ($adminRole) {
            $adminRole->syncPermissions($adminPermissions);
        }

        // Limited permissions for User
        $userPermissionNames = [
            'civil.view',
            'civil.update',
            'quick-count.view',
            'quick-count.create',
            'quick-count.update',
            'dashboard.view',
        ];
        $userPermissions = Permission::whereIn('name', $userPermissionNames)->get();
        if ($userRole) {
            $userRole->syncPermissions($userPermissions);
        }
    }
}
