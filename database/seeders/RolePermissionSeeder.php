<?php

namespace Database\Seeders;

use App\Services\PermissionSyncService;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $syncService = new PermissionSyncService();
        $syncService->syncRolesAndPermissions();
    }
}
