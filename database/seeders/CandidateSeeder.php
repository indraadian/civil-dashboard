<?php

namespace Database\Seeders;

use App\Models\Candidate;
use Illuminate\Database\Seeder;

class CandidateSeeder extends Seeder
{
    public function run(): void
    {
        $candidates = [
            [
                'number' => 1,
                'name' => 'H. Suherman & Bambang Irawan',
                'is_active' => true,
            ],
            [
                'number' => 2,
                'name' => 'Drs. H. Mulyadi & Hj. Siti Aminah',
                'is_active' => true,
            ],
            [
                'number' => 3,
                'name' => 'Dr. Ir. Hendra Wijaya & Agus Prasetyo',
                'is_active' => true,
            ],
        ];

        foreach ($candidates as $candidate) {
            Candidate::firstOrCreate(
                ['number' => $candidate['number']],
                $candidate
            );
        }
    }
}
