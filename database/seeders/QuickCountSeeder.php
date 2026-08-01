<?php

namespace Database\Seeders;

use App\Models\QuickCount;
use App\Models\Tps;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuickCountSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereIn('role', ['admin', 'super_admin'])->first() ?? User::first();

        // 1. Seed TPS Master Data
        $tpsDataList = [
            ['name' => 'TPS 01 - RW 01 Desa Sukamaju', 'code' => 'TPS-001', 'total_voters' => 350],
            ['name' => 'TPS 02 - RW 01 Desa Sukamaju', 'code' => 'TPS-002', 'total_voters' => 320],
            ['name' => 'TPS 03 - RW 02 Desa Sukamaju', 'code' => 'TPS-003', 'total_voters' => 400],
            ['name' => 'TPS 04 - RW 02 Desa Sukamaju', 'code' => 'TPS-004', 'total_voters' => 300],
            ['name' => 'TPS 05 - RW 03 Desa Sukamaju', 'code' => 'TPS-005', 'total_voters' => 380],
        ];

        foreach ($tpsDataList as $tpsData) {
            Tps::updateOrCreate(
                ['code' => $tpsData['code']],
                $tpsData
            );
        }

        // 2. Seed Sample Quick Count Entries
        $allTps = Tps::all();

        if ($allTps->isNotEmpty() && $admin) {
            // Seed TPS 01 results
            QuickCount::updateOrCreate(
                ['tps_id' => $allTps[0]->id],
                [
                    'vote_count' => 240,
                    'total_voters' => 350,
                    'notes' => 'Input sesuai C1 Plano TPS 01',
                    'created_by' => $admin->id,
                ]
            );

            // Seed TPS 02 results
            QuickCount::updateOrCreate(
                ['tps_id' => $allTps[1]->id],
                [
                    'vote_count' => 180,
                    'total_voters' => 320,
                    'notes' => 'Proses verifikasi saksi lancar',
                    'created_by' => $admin->id,
                ]
            );

            // Seed TPS 03 results
            QuickCount::updateOrCreate(
                ['tps_id' => $allTps[2]->id],
                [
                    'vote_count' => 310,
                    'total_voters' => 400,
                    'notes' => 'C1 Plano terverifikasi sah',
                    'created_by' => $admin->id,
                ]
            );
        }
    }
}
