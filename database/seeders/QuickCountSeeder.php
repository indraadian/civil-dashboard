<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\QuickCount;
use App\Models\QuickCountDetail;
use App\Models\Tps;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuickCountSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::whereIn('role', ['admin', 'super_admin'])->first() ?? User::first();
        $candidates = Candidate::where('is_active', true)->get();

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

        if ($allTps->isNotEmpty() && $admin && $candidates->isNotEmpty()) {
            $sampleEntries = [
                [
                    'tps_id' => $allTps[0]->id,
                    'officer_name' => 'Budi Santoso',
                    'officer_phone' => '081234567890',
                    'votes' => [120, 95, 75],
                    'invalid_votes' => 10,
                    'total_voters' => 300,
                ],
                [
                    'tps_id' => $allTps[1]->id,
                    'officer_name' => 'Siti Rahmawati',
                    'officer_phone' => '082345678901',
                    'votes' => [110, 85, 60],
                    'invalid_votes' => 5,
                    'total_voters' => 260,
                ],
                [
                    'tps_id' => $allTps[2]->id,
                    'officer_name' => 'Ahmad Fauzi',
                    'officer_phone' => '083456789012',
                    'votes' => [140, 105, 95],
                    'invalid_votes' => 10,
                    'total_voters' => 350,
                ],
            ];

            foreach ($sampleEntries as $entry) {
                $qc = QuickCount::updateOrCreate(
                    ['tps_id' => $entry['tps_id']],
                    [
                        'officer_name' => $entry['officer_name'],
                        'officer_phone' => $entry['officer_phone'],
                        'input_at' => now(),
                        'invalid_votes' => $entry['invalid_votes'],
                        'total_voters' => $entry['total_voters'],
                        'created_by' => $admin->id,
                    ]
                );

                foreach ($candidates as $index => $candidate) {
                    $voteCount = $entry['votes'][$index] ?? 50;
                    QuickCountDetail::updateOrCreate(
                        [
                            'quick_count_id' => $qc->id,
                            'candidate_id' => $candidate->id,
                        ],
                        [
                            'vote_count' => $voteCount,
                        ]
                    );
                }
            }
        }
    }
}
